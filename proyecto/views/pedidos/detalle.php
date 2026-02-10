<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
checkAuth();

$pedido = $_SESSION['pedido_actual'] ?? null;
$detalles = $_SESSION['detalles_pedido'] ?? [];

if (!$pedido) {
    header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
    exit;
}

// Limpiar las variables de sesión
unset($_SESSION['pedido_actual']);
unset($_SESSION['detalles_pedido']);

// Calcular subtotal (sin IVA)
$subtotal = $pedido['total'] / 1.19;
$iva = $pedido['total'] - $subtotal;

// Determinar la URL de retorno según el rol del usuario
$rol_usuario = $_SESSION['user']['rol'] ?? 'cliente';

switch ($rol_usuario) {
    case 'admin':
        $url_volver = '/Proyecto_aula/proyecto/views/admin/pedidos/index.php';
        $texto_volver = '← Volver al panel del Administrador';
        break;
    case 'chef':
        $url_volver = '/Proyecto_aula/proyecto/views/chef/dashboard.php';
        $texto_volver = '← Volver al Panel de Cocina';
        break;
    default: // cliente
        $url_volver = '/Proyecto_aula/proyecto/views/pedidos/index.php';
        $texto_volver = '← Volver a Mis Pedidos';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?= $pedido['id_pedido'] ?></title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pedidos.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Detalle del Pedido #<?= $pedido['id_pedido'] ?></h1>
            <a href="<?= $url_volver ?>" class="btn">
                <?= $texto_volver ?>
            </a>
        </div>

        <div class="pedido-info-detalle">
            <div class="info-grid">
                <div class="info-item-detalle">
                    <div class="info-label">Número de Pedido</div>
                    <div class="info-value">#<?= $pedido['id_pedido'] ?></div>
                </div>
                <div class="info-item-detalle">
                    <div class="info-label">Fecha</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></div>
                </div>
                <div class="info-item-detalle">
                    <div class="info-label">Cliente</div>
                    <div class="info-value"><?= htmlspecialchars($pedido['nombre_usuario']) ?></div>
                </div>
                <div class="info-item-detalle">
                    <div class="info-label">Estado</div>
                    <div class="info-value">
                        <?php
                        $estados_badge = [
                            'pendiente' => ['color' => '#f39c12', 'texto' => 'Pendiente'],
                            'preparando' => ['color' => '#3498db', 'texto' => 'Preparando'],
                            'listo' => ['color' => '#9b59b6', 'texto' => 'Listo'],
                            'entregado' => ['color' => '#27ae60', 'texto' => 'Entregado'],
                            'cancelado' => ['color' => '#e74c3c', 'texto' => 'Cancelado']
                        ];
                        $badge = $estados_badge[$pedido['estado']];
                        ?>
                        <span class="estado-badge estado-<?= $pedido['estado'] ?>" 
                              style="background-color: <?= $badge['color'] ?>20; color: <?= $badge['color'] ?>; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-block;">
                            <?= $badge['texto'] ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($rol_usuario === 'chef' || $rol_usuario === 'admin'): ?>
                <!-- Información adicional para chef/admin -->
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #f39c12;">
                    <p style="color: #856404; margin: 0; font-weight: 600;">
                        <?php if ($rol_usuario === 'chef'): ?>
                            👨‍🍳 <strong>Vista de Chef:</strong> Verifica los productos y el estado del pedido
                        <?php else: ?>
                            👑 <strong>Vista de Administrador:</strong> Gestión completa del pedido
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="detalles-section">
            <h2>Productos del Pedido</h2>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td>
                                <div class="producto-item">
                                    <?php if (!empty($detalle['imagen'])): ?>
                                        <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($detalle['imagen']) ?>" 
                                             alt="<?= htmlspecialchars($detalle['nombre_producto']) ?>"
                                             class="producto-img">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($detalle['nombre_producto']) ?></strong>
                                        <?php if (!empty($detalle['descripcion'])): ?>
                                            <br>
                                            <small style="color: #7f8c8d;"><?= htmlspecialchars($detalle['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                            <td><strong style="font-size: 18px;">x<?= $detalle['cantidad'] ?></strong></td>
                            <td><strong style="color: #27ae60; font-size: 16px;">$<?= number_format($detalle['subtotal'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="resumen">
                <div class="resumen-linea">
                    <span>Subtotal:</span>
                    <span><strong>$<?= number_format($subtotal, 2) ?></strong></span>
                </div>
                <div class="resumen-linea">
                    <span>IVA (19%):</span>
                    <span><strong>$<?= number_format($iva, 2) ?></strong></span>
                </div>
                <div class="resumen-total">
                    <span>Total Pagado:</span>
                    <span>$<?= number_format($pedido['total'], 2) ?></span>
                </div>
            </div>
        </div>

    </div>
</body>
</html>