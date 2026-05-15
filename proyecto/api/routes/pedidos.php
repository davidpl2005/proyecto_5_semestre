<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../middleware/verificar_token.php';
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/DetallePedido.php';
require_once __DIR__ . '/../../models/Carrito.php';

$method  = $_SERVER['REQUEST_METHOD'];
$usuario = obtenerUsuarioDelToken();
$id_usuario = $usuario['id'];

$pedidoModel = new Pedido();
$detalleModel = new DetallePedido();
$carritoModel = new Carrito();

// GET — listar pedidos o ver uno
if ($method === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    if ($id > 0) {
        $pedido = $pedidoModel->findById($id);

        if (!$pedido || $pedido['id_usuario'] != $id_usuario) {
            responderError('Pedido no encontrado', 404);
        }

        $detalles = $detalleModel->getByPedido($id);

        foreach ($detalles as &$detalle) {
            $detalle['imagen_url'] = !empty($detalle['imagen'])
                ? 'http://10.0.2.2/Proyecto_aula/proyecto/public/assets/img/products/' . $detalle['imagen']
                : null;
        }

        responder([
            'pedido'   => $pedido,
            'detalles' => $detalles
        ]);
    }

    $pedidos = $pedidoModel->getByUsuario($id_usuario);
    responder($pedidos);
}

// POST — crear pedido desde el carrito
if ($method === 'POST') {
    $carrito = $carritoModel->obtenerPorUsuario($id_usuario);

    if (empty($carrito)) {
        responderError('El carrito está vacío');
    }

    $subtotal = 0;
    foreach ($carrito as $item) {
        $subtotal += $item['subtotal'];
    }
    $total = $subtotal * 1.19;

    $id_pedido = $pedidoModel->create($id_usuario, $total, 'pendiente');

    if (!$id_pedido) {
        responderError('Error al crear el pedido', 500);
    }

    $items = [];
    foreach ($carrito as $item) {
        $items[] = [
            'id_producto' => $item['id_producto'],
            'cantidad'    => $item['cantidad'],
            'precio'      => $item['precio'],
            'subtotal'    => $item['subtotal']
        ];
    }

    if ($detalleModel->createMultiple($id_pedido, $items)) {
        $carritoModel->vaciar($id_usuario);
        responder([
            'mensaje'   => 'Pedido creado correctamente',
            'id_pedido' => $id_pedido,
            'total'     => round($total, 2)
        ], 201);
    } else {
        $pedidoModel->delete($id_pedido);
        responderError('Error al procesar el pedido', 500);
    }
}

responderError('Método no permitido', 405);