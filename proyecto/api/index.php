<?php
require_once __DIR__ . '/config/api.php';

// Leer la ruta solicitada
$ruta = $_GET['ruta'] ?? '';

switch ($ruta) {
    case 'auth':
        require __DIR__ . '/routes/auth.php';
        break;

    case 'productos':
        require __DIR__ . '/routes/productos.php';
        break;

    case 'carrito':
        require __DIR__ . '/routes/carrito.php';
        break;

    case 'pedidos':
        require __DIR__ . '/routes/pedidos.php';
        break;

    case 'pagos':
        require __DIR__ . '/routes/pagos.php';
        break;

    case 'facturas':
        require __DIR__ . '/routes/facturas.php';
        break;

    default:
        responderError('Ruta no encontrada', 404);
}