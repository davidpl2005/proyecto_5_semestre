<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../middleware/verificar_token.php';
require_once __DIR__ . '/../../models/Factura.php';
require_once __DIR__ . '/../../models/DetallePedido.php';

$method  = $_SERVER['REQUEST_METHOD'];
$usuario = obtenerUsuarioDelToken();
$id_usuario = $usuario['id'];

$facturaModel = new Factura();
$detalleModel = new DetallePedido();

if ($method === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    if ($id > 0) {
        $factura = $facturaModel->findById($id);

        if (!$factura || $factura['id_usuario'] != $id_usuario) {
            responderError('Factura no encontrada', 404);
        }

        $detalles = $detalleModel->getByPedido($factura['id_pedido']);

        responder([
            'factura'  => $factura,
            'detalles' => $detalles
        ]);
    }

    $facturas = $facturaModel->getByUsuario($id_usuario);
    responder($facturas);
}

responderError('Método no permitido', 405);