<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Opciones;
use App\Entity\Usuarios;

class RutinaController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mi-rutina", name="app_rutina", methods={"GET", "POST"})
     */
    public function rutinaController(Request $request): Response
    {
        // Obtener el usuario actualmente autenticado
        $user = $this->getUser();

        // Validar que el usuario está autenticado
        if (!$user) {
            throw $this->createAccessDeniedException('No se encontró un usuario autenticado.');
        }

        // Acceder al repositorio de Opciones para obtener los valores relacionados con este usuario
        $opcionesRepository = $this->entityManager->getRepository(Opciones::class);
        $opcion = $opcionesRepository->findOneBy(['id_usuario' => $user]);

        // Si no existe una entrada para el usuario, crear una con los valores por defecto (objetivo = 1, disponibilidad = 3)
        if (!$opcion) {
            $opcion = new Opciones();
            $opcion->setIdUsuario($user);  // Asignar el objeto Usuario completo
            $opcion->setObjetivo(1);  // Valor predeterminado para objetivo
            $opcion->setDisponibilidad(3);  // Valor predeterminado para disponibilidad

            // Persistir la nueva entrada
            $this->entityManager->persist($opcion);
            $this->entityManager->flush();

            // Mostrar un mensaje informando que se han creado las configuraciones por defecto
            return $this->render('rutina/rutina.html.twig', [
                'mensaje' => 'No se encontraron configuraciones previas. Se han creado configuraciones por defecto: Objetivo 1 y Disponibilidad 3.',
                'objetivoActual' => 1,
                'disponibilidadActual' => 3,
            ]);
        }

        // Obtener los valores de objetivo y disponibilidad actuales
        $objetivo = $opcion->getObjetivo();
        $disponibilidad = $opcion->getDisponibilidad();

        // Validar si los valores son nulos o vacíos
        if (empty($objetivo) || empty($disponibilidad)) {
            return $this->render('rutina/rutina.html.twig', [
                'mensaje' => 'Debe configurar su objetivo y disponibilidad para acceder a esta sección.',
            ]);
        }

        // Procesar el formulario si es un POST
        if ($request->isMethod('POST')) {
            $nuevoObjetivo = $request->request->get('objetivo');
            $nuevaDisponibilidad = $request->request->get('disponibilidad');

            // Validar que los valores recibidos son numéricos
            if (is_numeric($nuevoObjetivo) && is_numeric($nuevaDisponibilidad)) {
                $opcion->setObjetivo((int) $nuevoObjetivo);
                $opcion->setDisponibilidad((int) $nuevaDisponibilidad);

                // Persistir los cambios
                $this->entityManager->flush();

                // Redirigir a la misma ruta para evitar reenvíos del formulario
                return $this->redirectToRoute('app_rutina');
            }
        }

        // Determinar la plantilla según objetivo y disponibilidad
        $plantilla = $this->determinarPlantilla($objetivo, $disponibilidad);

        // Renderizar la plantilla correspondiente
        return $this->render($plantilla, [
            'titulo' => 'Mi Rutina',
            'objetivoActual' => $objetivo,
            'disponibilidadActual' => $disponibilidad,
        ]);
    }

    private function determinarPlantilla(int $objetivo, int $disponibilidad): string
    {
        $plantillas = [
            '1-3' => 'rutina/objetivo1_disponibilidad3.html.twig',
            '1-4' => 'rutina/objetivo1_disponibilidad4.html.twig',
            '1-5' => 'rutina/objetivo1_disponibilidad5.html.twig',
            '2-3' => 'rutina/objetivo2_disponibilidad3.html.twig',
            '2-4' => 'rutina/objetivo2_disponibilidad4.html.twig',
            '2-5' => 'rutina/objetivo2_disponibilidad5.html.twig',
            '3-3' => 'rutina/objetivo3_disponibilidad3.html.twig',
            '3-4' => 'rutina/objetivo3_disponibilidad4.html.twig',
            '3-5' => 'rutina/objetivo3_disponibilidad5.html.twig',
        ];

        $clave = "{$objetivo}-{$disponibilidad}";
        return $plantillas[$clave] ?? 'rutina/default.html.twig';
    }
}
