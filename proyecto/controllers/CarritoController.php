<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Carrito.php';
require_once __DIR__ . '/../models/Inventario.php';

checkAuth();

$productoModel = new Producto();
$carritoModel = new Carrito();
$inventarioModel = new Inventario();
$action = $_GET['action'] ?? '';

$id_usuario = $_SESSION['user']['id'];

switch ($action) {
    case 'add':
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

            // VALIDAR STOCK DISPONIBLE
            $inventario = $inventarioModel->findByProducto($id_producto);
            
            if (!$inventario) {
                $_SESSION['error'] = 'No hay información de inventario para este producto';
                header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
                exit;
            }

            $stock_disponible = $inventario['cantidad'];

            if ($stock_disponible <= 0) {
                $_SESSION['error'] = 'Producto agotado';
                header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
                exit;
            }

            // Verificar si ya está en el carrito para sumar cantidades
            $carrito_actual = $carritoModel->obtenerPorUsuario($id_usuario);
            $cantidad_en_carrito = 0;
            
            foreach ($carrito_actual as $item) {
                if ($item['id_producto'] == $id_producto) {
                    $cantidad_en_carrito = $item['cantidad'];
                    break;
                }
            }

            $cantidad_total = $cantidad_en_carrito + $cantidad;

            if ($cantidad_total > $stock_disponible) {
                $_SESSION['error'] = "Solo hay {$stock_disponible} unidades disponibles de este producto";
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if ($cantidad <= 0) {
                $_SESSION['error'] = 'La cantidad debe ser mayor a 0';
                header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
                exit;
            }

            // VALIDAR STOCK DISPONIBLE
            $inventario = $inventarioModel->findByProducto($id_producto);
            
            if ($inventario && $cantidad > $inventario['cantidad']) {
                $_SESSION['error'] = "Solo hay {$inventario['cantidad']} unidades disponibles";
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
        if ($carritoModel->vaciar($id_usuario)) {
            $_SESSION['success'] = 'Carrito vaciado';
        } else {
            $_SESSION['error'] = 'Error al vaciar el carrito';
        }
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    case 'view':
        header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        exit;

    default:
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
}