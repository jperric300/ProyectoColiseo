<?php
namespace App\Controller;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Productos;
use App\Entity\Pedidos;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TiendaController extends AbstractController
{
    private $entityManager;
    private $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/tienda", name="app_tienda")
     */
    public function tiendaController(Request $request): Response
    {
        // Verificar si el usuario está autenticado
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Obtener todos los productos de la base de datos
        $productos = $this->entityManager->getRepository(Productos::class)->findAll();
        $session = $this->requestStack->getSession();

        // Inicializar el carrito si no existe
        if (!$session->has('carrito')) {
            $session->set('carrito', []);
        }

        if ($request->isMethod('POST')) {
            $productoId = $request->request->get('producto_id');
            $accion = $request->request->get('accion');
            $carrito = $session->get('carrito', []);

            if (!$accion || $accion !== 'eliminar') {
                $cantidad = $request->request->get('cantidad');
                $producto = $this->entityManager->getRepository(Productos::class)->find($productoId);

                if ($producto && $cantidad > 0 && $cantidad <= $producto->getStock()) {
                    if (isset($carrito[$productoId])) {
                        $carrito[$productoId] += $cantidad;
                    } else {
                        $carrito[$productoId] = $cantidad;
                    }
                    $session->set('carrito', $carrito);
                } else {
                    $this->addFlash('error', 'Cantidad no válida o producto no encontrado.');
                }
            } elseif ($accion === 'eliminar') {
                if (isset($carrito[$productoId])) {
                    unset($carrito[$productoId]);
                    $session->set('carrito', $carrito);
                }
            }

            if ($request->isXmlHttpRequest()) {
                $carritoDetalles = [];
                $total = 0;
                foreach ($carrito as $id => $qty) {
                    $producto = $this->entityManager->getRepository(Productos::class)->find($id);
                    if ($producto) {
                        $carritoDetalles[] = [
                            'id' => $producto->getId(),
                            'nombre' => $producto->getNombre(),
                            'cantidad' => $qty,
                            'subtotal' => $qty * $producto->getPrecio(),
                        ];
                        $total += $qty * $producto->getPrecio();
                    }
                }

                return $this->json([
                    'success' => true,
                    'carrito' => $carritoDetalles,
                    'total' => $total,
                ]);
            }

            $this->addFlash('success', 'Producto actualizado en el carrito.');
        }

        return $this->render('tienda/tienda.html.twig', [
            'productos' => $productos,
            'carrito' => $session->get('carrito'),
        ]);
    }

    /**
     * @Route("/confirmacion", name="app_confirmacion")
     */
    public function confirmacion(): Response
    {
        // Verificar si el usuario está autenticado
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $session = $this->requestStack->getSession();
        $carrito = $session->get('carrito', []);

        $productosEnCarrito = [];
        $total = 0;

        foreach ($carrito as $productoId => $cantidad) {
            $producto = $this->entityManager->getRepository(Productos::class)->find($productoId);
            if ($producto) {
                $subtotal = $cantidad * $producto->getPrecio();
                $productosEnCarrito[] = [
                    'id' => $producto->getId(),
                    'nombre' => $producto->getNombre(),
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            }
        }

        return $this->render('tienda/confirmacion.html.twig', [
            'productosEnCarrito' => $productosEnCarrito,
            'total' => $total,
        ]);
    }

   /**
 * @Route("/pagar", name="app_pagar", methods={"POST"})
 */
public function pagar(MailerInterface $mailer): Response
{
    // Verificar si el usuario está autenticado
    if (!$this->getUser()) {
        return $this->redirectToRoute('app_login');
    }

    $session = $this->requestStack->getSession();
    $carrito = $session->get('carrito', []);

    // Verificar que el carrito no esté vacío
    if (empty($carrito)) {
        $this->addFlash('error', 'Tu carrito está vacío.');
        return $this->redirectToRoute('app_tienda');
    }

    // Obtener el correo del usuario desde la sesión o de otra manera (por ejemplo, el objeto de usuario autenticado)
    $correoUsuario = $this->getUser()->getCorreoElectronico();

    // Preparar los detalles de la compra
    $productosEnCarrito = [];
    $total = 0;
    $productos = []; // IDs de productos
    $cantidades = []; // Cantidades correspondientes

    foreach ($carrito as $productoId => $cantidad) {
        $producto = $this->entityManager->getRepository(Productos::class)->find($productoId);
        if ($producto) {
            $subtotal = $cantidad * $producto->getPrecio();
            $productosEnCarrito[] = [
                'nombre' => $producto->getNombre(),
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
            ];
            $productos[] = $producto->getId();
            $cantidades[] = $cantidad;
            $total += $subtotal;
        }
    }

    // Procesar la compra y actualizar el stock
    foreach ($carrito as $productoId => $cantidad) {
        $producto = $this->entityManager->getRepository(Productos::class)->find($productoId);
        if ($producto) {
            $nuevoStock = $producto->getStock() - $cantidad;

            // Si el stock es insuficiente
            if ($nuevoStock < 0) {
                $this->addFlash('error', 'No hay suficiente stock para el producto: ' . $producto->getNombre());
                return $this->redirectToRoute('app_tienda');
            }

            // Actualizar el stock
            $producto->setStock($nuevoStock);
            $this->entityManager->persist($producto);
        }
    }

    // Guardar el pedido en la base de datos
    $pedido = new Pedidos();
    $pedido->setUserId($this->getUser()->getId());
    $pedido->setProductos($productos);  // Lista de IDs de productos
    $pedido->setCantidades($cantidades); // Lista de cantidades
    $pedido->setPrecioTotal($total);

    // Persistir el pedido
    $this->entityManager->persist($pedido);
    $this->entityManager->flush();

    // Limpiar el carrito de la sesión
    $session->remove('carrito');

    // Crear el mensaje de correo
    $email = (new Email())
        ->from('elcoliseoshop@gmail.com') // El correo desde el que se enviará el mensaje
        ->to($correoUsuario)  // El correo del usuario
        ->subject('Confirmación de Compra')
        ->html($this->renderView('tienda/correo_confirmacion.html.twig', [
            'productosEnCarrito' => $productosEnCarrito,
            'total' => $total,
        ]));

    // Enviar el correo
    $mailer->send($email);

    // Mostrar mensaje de éxito
    $this->addFlash('success', 'Compra realizada con éxito. Los productos han sido descontados del stock. Un correo de confirmación ha sido enviado.');

    // Redirigir a la tienda o a una página de confirmación
    return $this->redirectToRoute('app_tienda');
}

}
