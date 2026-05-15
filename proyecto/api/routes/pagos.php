<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../middleware/verificar_token.php';
require_once __DIR__ . '/../../models/Pago.php';
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Factura.php';

$method  = $_SERVER['REQUEST_METHOD'];
$usuario = obtenerUsuarioDelToken();
$id_usuario = $usuario['id'];

$pagoModel   = new Pago();
$pedidoModel = new Pedido();
$facturaModel = new Factura();

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $id_pedido   = intval($body['id_pedido'] ?? 0);
    $metodo_pago = $body['metodo_pago'] ?? '';
    $monto       = floatval($body['monto'] ?? 0);

    $metodos_validos = ['efectivo', 'tarjeta', 'transferencia'];
    if (!in_array($metodo_pago, $metodos_validos)) {
        responderError('Método de pago inválido');
    }

    $pedido = $pedidoModel->findById($id_pedido);
    if (!$pedido || $pedido['id_usuario'] != $id_usuario) {
        responderError('Pedido no encontrado', 404);
    }

    if ($pagoModel->pedidoTienePago($id_pedido)) {
        responderError('Este pedido ya fue pagado');
    }

    if (abs($monto - $pedido['total']) > 0.01) {
        responderError('El monto no coincide con el total del pedido');
    }

    $id_pago = $pagoModel->create($id_pedido, $metodo_pago, $monto, 'completado');

    if (!$id_pago) {
        responderError('Error al procesar el pago', 500);
    }

    // Generar factura automáticamente
    $subtotal = $monto / 1.19;
    $iva      = $monto - $subtotal;

    $id_factura = $facturaModel->create(
        $id_pedido,
        $id_pago,
        $id_usuario,
        $subtotal,
        $iva,
        $monto,
        'Factura generada desde app móvil'
    );

    $pago = $pagoModel->findById($id_pago);

    responder([
        'mensaje'     => 'Pago procesado correctamente',
        'id_pago'     => $id_pago,
        'referencia'  => $pago['referencia'] ?? '',
        'id_factura'  => $id_factura
    ], 201);
}

responderError('Método no permitido', 405);