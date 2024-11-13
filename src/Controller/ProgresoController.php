<?php
namespace App\Controller;

use App\Entity\Progreso;
use App\Entity\Usuarios;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProgresoController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mi-progreso", name="app_progreso", methods={"GET", "POST"})
     */
    public function progresoController(Request $request): Response
    {
        // Obtener el usuario autenticado
        $usuario = $this->getUser();

        if ($usuario === null) {
            return $this->redirectToRoute('app_login'); // Redirigir si no hay usuario autenticado
        }

        // Manejar el formulario de peso
        if ($request->isMethod('POST')) {
            // Verificar si se envió el formulario de eliminación
            if ($request->request->has('eliminar')) {
                $id = $request->request->get('eliminar');
                return $this->eliminarProgreso($id);
            }

            $nuevoPeso = $request->request->get('peso');
            $pesoAnterior = $this->getLastPeso($usuario); // Obtener el último peso del usuario

            // Crear nueva entrada de progreso
            $progreso = new Progreso();
            $progreso->setUsuario($usuario);
            $progreso->setPeso($nuevoPeso);
            $progreso->setFecha(new \DateTime());

            $this->entityManager->persist($progreso);
            $this->entityManager->flush();

            // Mensaje motivador
            if ($pesoAnterior !== null) {
                if ($nuevoPeso > $pesoAnterior) {
                    $mensaje = "¡Sigue intentándolo! Tu peso ha aumentado. Recuerda que cada paso cuenta.";
                } elseif ($nuevoPeso < $pesoAnterior) {
                    $mensaje = "¡Genial! Has bajado de peso. ¡Sigue así!";
                }

                // Añadir el mensaje a la sesión para mostrarlo después del redireccionamiento
                $this->addFlash('info', $mensaje);
            }

            return $this->redirectToRoute('app_progreso'); // Redirigir para evitar reenvío de formulario
        }

        // Obtener todos los progresos del usuario almacenados para mostrar
        $progresos = $this->getProgresos($usuario);

        return $this->render('progreso/progreso.html.twig', [
            'titulo' => 'Mi Progreso',
            'progresos' => $progresos,
        ]);
    }

    private function eliminarProgreso(int $id): Response
    {
        $progreso = $this->entityManager->getRepository(Progreso::class)->find($id);

        if (!$progreso || $progreso->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_progreso'); // Redirige si el registro no existe o no pertenece al usuario actual
        }

        $this->entityManager->remove($progreso);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_progreso');
    }

    private function getLastPeso(Usuarios $usuario): ?string
    {
        return $this->entityManager->getRepository(Progreso::class)
            ->findOneBy(['usuario' => $usuario], ['fecha' => 'DESC'])?->getPeso();
    }

    private function getProgresos(Usuarios $usuario): array
    {
        return $this->entityManager->getRepository(Progreso::class)
            ->findBy(['usuario' => $usuario], ['fecha' => 'DESC']);
    }
}
