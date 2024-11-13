<?php

namespace App\Controller;

use App\Entity\Productos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PaginaPrincipalController extends AbstractController
{
    /**
     * @Route("/", name="app_pagina_principal", methods={"GET"})
     */
    public function paginaPrincipal(EntityManagerInterface $entityManager): Response
    {
        // Obtener los productos con los ID 1, 2 y 4
        $productosDestacados = $entityManager->getRepository(Productos::class)->findBy(['id' => [1, 2, 4]]);

        return $this->render('paginaPrincipal/paginaPrincipal.html.twig', [
            'productosDestacados' => $productosDestacados,
        ]);
    }

    /**
     * @Route("/mi-rutina", name="app_rutina", methods={"GET"})
     */
    public function rutina(): Response
    {
        return $this->render('paginaPrincipal/rutina.html.twig');
    }

    /**
     * @Route("/tienda", name="app_tienda", methods={"GET"})
     */
    public function tienda(): Response
    {
        return $this->render('paginaPrincipal/tienda.html.twig');
    }

    /**
     * @Route("/mi-progreso", name="app_progreso", methods={"GET"})
     */
    public function progreso(): Response
    {
        return $this->render('paginaPrincipal/progreso.html.twig');
    }
}
