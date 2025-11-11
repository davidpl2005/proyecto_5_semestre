<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Producto.php';

checkAuth(); // Verificar que el usuario esté autenticado

$productoModel = new Producto();
$action = $_GET['action'] ?? '';

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

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

            // Verificar si el producto ya está en el carrito
            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id_producto'] == $id_producto) {
                    $item['cantidad'] += $cantidad;
                    $item['subtotal'] = $item['cantidad'] * $item['precio'];
                    $encontrado = true;
                    break;
                }
            }

            // Si no está en el carrito, agregarlo
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id_producto' => $producto['id_producto'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'cantidad' => $cantidad,
                    'subtotal' => $producto['precio'] * $cantidad
                ];
            }

            $_SESSION['success'] = 'Producto agregado al carrito';
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

            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id_producto'] == $id_producto) {
                    $item['cantidad'] = $cantidad;
                    $item['subtotal'] = $item['cantidad'] * $item['precio'];
                    $_SESSION['success'] = 'Cantidad actualizada';
                    break;
                }
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'remove':
        // Eliminar un producto del carrito
        $id_producto = intval($_GET['id'] ?? 0);

        if ($id_producto > 0) {
            foreach ($_SESSION['carrito'] as $key => $item) {
                if ($item['id_producto'] == $id_producto) {
                    unset($_SESSION['carrito'][$key]);
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar array
                    $_SESSION['success'] = 'Producto eliminado del carrito';
                    break;
                }
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'clear':
        // Vaciar todo el carrito
        $_SESSION['carrito'] = [];
        $_SESSION['success'] = 'Carrito vaciado';
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