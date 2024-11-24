<?php

namespace App\Controller;

use App\Entity\Opciones;
use App\Entity\Usuarios;
use App\Form\DisponibilidadFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DisponibilidadController extends AbstractController
{
    /**
     * @Route("/disponibilidad", name="app_disponibilidad", methods={"GET", "POST"})
     */
    public function disponibilidad(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        // Recuperar el usuario_id y la opción desde la sesión
        $usuario_id = $session->get('usuario_id');
        $option = $session->get('option');

        // Verificar que los datos estén en la sesión
        if (!$usuario_id || !$option) {
            return $this->redirectToRoute('app_home'); // Cambia 'app_home' a la ruta deseada
        }

        // Buscar la entidad Usuarios a partir del ID
        $usuario = $entityManager->getRepository(Usuarios::class)->find($usuario_id);

        // Verificar si se encontró el usuario
        if (!$usuario) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('app_home'); // Cambia a la ruta deseada
        }

        // Buscar si ya existe una disponibilidad para este usuario
        $disponibilidad = $entityManager->getRepository(Opciones::class)->findOneBy(['id_usuario' => $usuario]);

        // Crear el formulario
        $form = $this->createForm(DisponibilidadFormType::class);

        // Manejar la petición
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtener los días seleccionados y el objetivo desde el formulario
            $data = $form->getData();
            $diasSeleccionados = $data['diasDisponibles'];

            // Mostrar el número de días seleccionados en un mensaje flash
            $this->addFlash('success', 'Has seleccionado ' . $diasSeleccionados . ' días.');

            // Si no existe una disponibilidad para este usuario, crearla
            if (!$disponibilidad) {
                $disponibilidad = new Opciones();
                $disponibilidad->setIdUsuario($usuario); // Asignamos la entidad Usuarios
                $disponibilidad->setObjetivo($option); // Establecemos el objetivo
            }

            // Actualizar la disponibilidad con los nuevos días seleccionados
            $disponibilidad->setDisponibilidad($diasSeleccionados); // Asegúrate de que este método existe

            // Persistir los cambios (si existe, actualizar; si no, crear)
            $entityManager->persist($disponibilidad);
            $entityManager->flush();

            // Redirigir a la página principal después de guardar
            return $this->redirectToRoute('app_login');
        }

        return $this->render('disponibilidad/disponibilidad.html.twig', [
            'disponibilidadForm' => $form->createView(),
            'usuario_id' => $usuario_id,
            'option' => $option,
        ]);
    }
}
