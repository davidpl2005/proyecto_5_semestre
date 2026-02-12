<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Carrito.php';

checkAuth(); // Verificar que el usuario esté autenticado

$productoModel = new Producto();
$carritoModel = new Carrito();
$action = $_GET['action'] ?? '';

$id_usuario = $_SESSION['user']['id'];

switch ($action) {
    case 'add':
        // Agregar producto al carrito
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if ($id_producto <= 0 || $cantidad <= 0) {
                $_SESSION['error'] = 'Datos inválidos';
                header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
                exit;
            }

            // Obtener información del producto
            $producto = $productoModel->findById($id_producto);

            if (!$producto) {
                $_SESSION['error'] = 'Producto no encontrado';
                header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
                exit;
            }

            if (!$producto['disponible']) {
                $_SESSION['error'] = 'Producto no disponible';
                header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
                exit;
            }

            // Agregar a la base de datos
            if ($carritoModel->agregar($id_usuario, $id_producto, $cantidad, $producto['precio'])) {
                $_SESSION['success'] = 'Producto agregado al carrito';
            } else {
                $_SESSION['error'] = 'Error al agregar el producto';
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;

    case 'update':
        // Actualizar cantidad de un producto en el carrito
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if ($cantidad <= 0) {
                $_SESSION['error'] = 'La cantidad debe ser mayor a 0';
                header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
                exit;
            }

            if ($carritoModel->actualizarCantidad($id_usuario, $id_producto, $cantidad)) {
                $_SESSION['success'] = 'Cantidad actualizada';
            } else {
                $_SESSION['error'] = 'Error al actualizar la cantidad';
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'remove':
        // Eliminar un producto del carrito
        $id_producto = intval($_GET['id'] ?? 0);

        if ($id_producto > 0) {
            if ($carritoModel->eliminar($id_usuario, $id_producto)) {
                $_SESSION['success'] = 'Producto eliminado del carrito';
            } else {
                $_SESSION['error'] = 'Error al eliminar el producto';
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'clear':
        // Vaciar todo el carrito
        if ($carritoModel->vaciar($id_usuario)) {
            $_SESSION['success'] = 'Carrito vaciado';
        } else {
            $_SESSION['error'] = 'Error al vaciar el carrito';
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'view':
        // Redirigir a la vista del carrito
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    default:
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
}