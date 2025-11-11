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
            <a href="<?= $_SESSION['user']['rol'] === 'admin' ? '/Proyecto_aula/proyecto/views/admin/pedidos/index.php' : '/Proyecto_aula/proyecto/views/pedidos/index.php' ?>" 
               class="btn">
                ← Volver a Pedidos
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
                        <span class="estado-badge estado-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                </div>
            </div>
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
                                    </div>
                                </div>
                            </td>
                            <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                            <td><strong>x<?= $detalle['cantidad'] ?></strong></td>
                            <td><strong style="color: #27ae60;">$<?= number_format($detalle['subtotal'], 2) ?></strong></td>
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