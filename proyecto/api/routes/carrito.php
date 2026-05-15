<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../middleware/verificar_token.php';
require_once __DIR__ . '/../../models/Carrito.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Inventario.php';

$method  = $_SERVER['REQUEST_METHOD'];
$usuario = obtenerUsuarioDelToken();
$id_usuario = $usuario['id'];

$carritoModel   = new Carrito();
$productoModel  = new Producto();
$inventarioModel = new Inventario();

// GET — obtener carrito del usuario
if ($method === 'GET') {
    $items = $carritoModel->obtenerPorUsuario($id_usuario);

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['imagen_url'] = !empty($item['imagen'])
            ? 'http://10.0.2.2/Proyecto_aula/proyecto/public/assets/img/products/' . $item['imagen']
            : null;
        $subtotal += $item['subtotal'];
    }

    $iva   = $subtotal * 0.19;
    $total = $subtotal + $iva;

    responder([
        'items'    => $items,
        'subtotal' => round($subtotal, 2),
        'iva'      => round($iva, 2),
        'total'    => round($total, 2)
    ]);
}

// POST — agregar producto al carrito
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $id_producto = intval($body['id_producto'] ?? 0);
    $cantidad    = intval($body['cantidad'] ?? 1);

    if ($id_producto <= 0 || $cantidad <= 0) {
        responderError('Datos inválidos');
    }

    $producto = $productoModel->findById($id_producto);
    if (!$producto || !$producto['disponible']) {
        responderError('Producto no disponible');
    }

    $inventario = $inventarioModel->findByProducto($id_producto);
    if (!$inventario || $inventario['cantidad'] <= 0) {
        responderError('Producto agotado');
    }

    // Verificar stock vs cantidad en carrito
    $carrito_actual = $carritoModel->obtenerPorUsuario($id_usuario);
    $en_carrito = 0;
    foreach ($carrito_actual as $item) {
        if ($item['id_producto'] == $id_producto) {
            $en_carrito = $item['cantidad'];
            break;
        }
    }

    if (($en_carrito + $cantidad) > $inventario['cantidad']) {
        responderError('Stock insuficiente. Disponibles: ' . $inventario['cantidad']);
    }

    if ($carritoModel->agregar($id_usuario, $id_producto, $cantidad, $producto['precio'])) {
        responder(['mensaje' => 'Producto agregado al carrito']);
    } else {
        responderError('Error al agregar producto', 500);
    }
}

// PUT — actualizar cantidad
if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true);

    $id_producto = intval($body['id_producto'] ?? 0);
    $cantidad    = intval($body['cantidad'] ?? 1);

    if ($cantidad <= 0) {
        responderError('La cantidad debe ser mayor a 0');
    }

    $inventario = $inventarioModel->findByProducto($id_producto);
    if ($inventario && $cantidad > $inventario['cantidad']) {
        responderError('Stock insuficiente. Disponibles: ' . $inventario['cantidad']);
    }

    if ($carritoModel->actualizarCantidad($id_usuario, $id_producto, $cantidad)) {
        responder(['mensaje' => 'Cantidad actualizada']);
    } else {
        responderError('Error al actualizar', 500);
    }
}

// DELETE — eliminar producto o vaciar
if ($method === 'DELETE') {
    $id_producto = intval($_GET['id_producto'] ?? 0);

    if ($id_producto > 0) {
        $carritoModel->eliminar($id_usuario, $id_producto);
        responder(['mensaje' => 'Producto eliminado del carrito']);
    } else {
        $carritoModel->vaciar($id_usuario);
        responder(['mensaje' => 'Carrito vaciado']);
    }
}

responderError('Método no permitido', 405);