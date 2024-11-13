<?php

namespace App\Controller;

use App\Entity\Opciones; // Asegúrate de tener la entidad correcta
use App\Entity\Usuarios; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ObjectivesController extends AbstractController
{
    /**
     * @Route("/options", name="app_options")
     */
    public function options(SessionInterface $session): Response
    {
        $usuario_id = $session->get('usuario_id');

        if (!$usuario_id) {
            throw $this->createNotFoundException('Usuario ID es requerido.');
        }

        return $this->render('option/objectives.html.twig', [
            'usuario_id' => $usuario_id,
        ]);
    }

    /**
     * @Route("/confirm-option", name="app_confirm_option", methods={"POST"})
     */
    public function confirmOption(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $usuario_id = $session->get('usuario_id');

        if (!$usuario_id) {
            throw $this->createNotFoundException('Usuario ID es requerido.');
        }

        $option = $request->request->get('option');

        // Mapeo de opciones de string a int
        $optionMap = [
            'perdida_peso' => 1,
            'mantener_masa' => 2,
            'ganar_masa' => 3,
        ];

        if (isset($optionMap[$option])) {
            $optionValue = $optionMap[$option];

            // Obtener el objeto Usuarios correspondiente al usuario_id
            $usuario = $entityManager->getRepository(Usuarios::class)->find($usuario_id);

            if (!$usuario) {
                throw $this->createNotFoundException('Usuario no encontrado.');
            }

            // Crear una nueva instancia de Opciones
            $opcion = new Opciones();
            $opcion->setObjetivo($optionValue); // Establecer el valor numérico
            $opcion->setIdUsuario($usuario); // Establecer el objeto Usuarios

            // Persistir la opción en la base de datos
            $entityManager->persist($opcion);
            $entityManager->flush();

            // Guardar la opción seleccionada como número en la sesión
            $session->set('option', $optionValue); // Cambiado a guardar el número

            $this->addFlash('success', 'Has seleccionado: ' . $optionValue); // Mensaje actualizado

            // Redirigir a la ruta de disponibilidad sin pasar parámetros en la URL
            return $this->redirectToRoute('app_disponibilidad');
        } else {
            $this->addFlash('error', 'Opción inválida. Por favor selecciona una opción válida.');
        }

        return $this->redirectToRoute('app_options');
    }
}
