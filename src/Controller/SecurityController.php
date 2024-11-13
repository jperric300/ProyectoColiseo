<?php

namespace App\Controller;

use App\Entity\Usuarios;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si el usuario ya está autenticado, lo redirigimos a la página de inicio
         if ($this->getUser()) {
            return $this->redirectToRoute('app_pagina_principal');
        }

        // Obtener el error de inicio de sesión si lo hay
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = $error ? 'Credenciales inválidas. Inténtalo de nuevo.' : null;

        // Obtener el último nombre de usuario ingresado
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $errorMessage, // Mensaje de error personalizado
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony maneja el logout automáticamente, este método puede estar vacío
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
