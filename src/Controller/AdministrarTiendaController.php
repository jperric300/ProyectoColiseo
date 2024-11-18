<?php

namespace App\Controller;

use App\Entity\Productos;
use App\Repository\ProductosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tienda')]
#[IsGranted('ROLE_ADMIN')]
class AdministrarTiendaController extends AbstractController
{
    private $productosRepository;

    // Inyectamos el repositorio de productos
    public function __construct(ProductosRepository $productosRepository)
    {
        $this->productosRepository = $productosRepository;
    }

    #[Route('/', name: 'admin_productos_index')]
    public function index(): Response
    {
        // Obtener todos los productos
        $productos = $this->productosRepository->findAll();

        // Renderizar la vista con los productos
        return $this->render('administrar/tienda.html.twig', [
            'productos' => $productos,
        ]);
    }

    #[Route('/guardar', name: 'admin_productos_guardar', methods: ['POST'])]
    public function guardar(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Obtener datos enviados desde el formulario como array
        $productosData = $request->request->all('productos');

        // Iterar por cada producto y actualizar o crear nuevos productos
        foreach ($productosData as $id => $datos) {
            if ($id === 'new') {
                // Crear un nuevo producto
                $producto = new Productos();
                $producto->setNombre($datos['nombre']);
                $producto->setDescripcion($datos['descripcion']);
                $producto->setPrecio((float)$datos['precio']);
                $producto->setImagen($datos['imagen']);
                $producto->setStock((int)$datos['stock']);
                $entityManager->persist($producto);
            } else {
                // Actualizar producto existente
                $producto = $this->productosRepository->find($id);
                if ($producto) {
                    $producto->setNombre($datos['nombre']);
                    $producto->setDescripcion($datos['descripcion']);
                    $producto->setPrecio((float)$datos['precio']);
                    $producto->setImagen($datos['imagen']);
                    $producto->setStock((int)$datos['stock']);
                }
            }
        }

        // Guardar cambios en la base de datos
        $entityManager->flush();

        // Redirigir con un mensaje de éxito
        $this->addFlash('success', 'Productos actualizados o añadidos con éxito.');
        return $this->redirectToRoute('admin_productos_index');
    }

    #[Route('/eliminar/{id}', name: 'admin_productos_eliminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function eliminar(int $id, EntityManagerInterface $entityManager): Response
    {
        // Buscar el producto por su ID
        $producto = $this->productosRepository->find($id);

        // Si el producto existe, eliminarlo
        if ($producto) {
            $entityManager->remove($producto);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado con éxito.');
        } else {
            $this->addFlash('error', 'El producto no existe.');
        }

        // Redirigir de vuelta al listado de productos
        return $this->redirectToRoute('admin_productos_index');
    }
}
