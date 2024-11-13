<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RutinaController extends AbstractController
{
    /**
     * @Route("/mi-rutina", name="app_rutina", methods={"GET"})
     */
    public function rutinaController(): Response
    {
        // Renderizar la vista de la página de "Mi Rutina"
        return $this->render('rutina/rutina.html.twig', [
            'titulo' => 'Mi Rutina',
        ]);
    }
}