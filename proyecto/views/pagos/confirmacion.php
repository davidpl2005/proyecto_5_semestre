<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Pago.php';
require_once __DIR__ . '/../../models/Pedido.php';
checkAuth();

$id_pago = intval($_GET['id_pago'] ?? 0);

if (!$id_pago) {
    header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
    exit;
}

$pagoModel = new Pago();
$pedidoModel = new Pedido();

$pago = $pagoModel->findById($id_pago);

if (!$pago) {
    $_SESSION['error'] = 'Pago no encontrado';
    header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
    exit;
}

// Verificar que el pago pertenece al usuario
$pedido = $pedidoModel->findById($pago['id_pedido']);
if ($pedido['id_usuario'] != $_SESSION['user']['id']) {
    $_SESSION['error'] = 'No tienes permiso para ver este pago';
    header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
    exit;
}

// Mapeo de métodos de pago a nombres legibles
$metodos = [
    'efectivo' => 'Efectivo',
    'tarjeta' => 'Tarjeta de Crédito/Débito',
    'transferencia' => 'Transferencia Bancaria'
];

$metodo_nombre = $metodos[$pago['metodo_pago']] ?? $pago['metodo_pago'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Confirmado - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pagos.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="confirmacion-pago">
        <div class="success-icon-pago">
            <span class="checkmark-pago">✓</span>
        </div>

        <h1>¡Pago Confirmado!</h1>
        <p style="color: #7f8c8d; margin-bottom: 20px;">
            Tu pago ha sido procesado exitosamente
        </p>

        <div class="referencia-pago">
            📄 Referencia: <?= htmlspecialchars($pago['referencia']) ?>
        </div>

        <div class="detalle-pago-confirmacion">
            <p><strong>Pedido:</strong> #<?= $pago['id_pedido'] ?></p>
            <p><strong>Método de pago:</strong> <?= htmlspecialchars($metodo_nombre) ?></p>
            <p><strong>Monto pagado:</strong> <strong style="color: #27ae60; font-size: 20px;">$<?= number_format($pago['monto'], 2) ?></strong></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></p>
            <p><strong>Estado:</strong> <span style="color: #27ae60; font-weight: 600;">✓ Completado</span></p>
        </div>

        <div style="background-color: #e8f5e9; padding: 20px; border-radius: 8px; margin: 30px 0; text-align: left;">
            <h3 style="color: #27ae60; font-size: 16px; margin-bottom: 10px;">¿Qué sigue ahora?</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 8px 0; color: #2c3e50;">✓ Tu pedido está siendo preparado</li>
                <li style="padding: 8px 0; color: #2c3e50;">✓ Puedes ver el estado en "Mis Pedidos"</li>
                <li style="padding: 8px 0; color: #2c3e50;">✓ Te notificaremos cuando esté listo</li>
                <li style="padding: 8px 0; color: #2c3e50;">✓ Guarda tu referencia de pago</li>
            </ul>
        </div>

        <?php if (isset($_SESSION['id_factura_generada'])): ?>
            <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=ver&id=<?= $_SESSION['id_factura_generada'] ?>"
                class="btn-primary">
                🧾 Ver Factura
            </a>
            <?php unset($_SESSION['id_factura_generada']); ?>
        <?php endif; ?>


        <div class="acciones-confirmacion">
            <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pago['id_pedido'] ?>"
                class="btn-primary">
                📋 Ver Detalle del Pedido
            </a>
            <a href="/Proyecto_aula/proyecto/views/pedidos/index.php"
                class="btn-secondary">
                📦 Mis Pedidos
            </a>
            <a href="/Proyecto_aula/proyecto/views/menu/index.php"
                class="btn-secondary">
                🍽️ Volver al Menú
            </a>
        </div>
    </div>
</body>

</html>