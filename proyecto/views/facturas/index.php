<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Factura.php';
checkAuth();

$facturaModel = new Factura();
$facturas = $facturaModel->getByUsuario($_SESSION['user']['id']);

// Mapeo de métodos de pago
$metodos = [
    'efectivo' => '💵 Efectivo',
    'tarjeta' => '💳 Tarjeta',
    'transferencia' => '🏦 Transferencia'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Facturas - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/facturas.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Mis Facturas</h1>
            <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn">← Volver al Menú</a>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="facturas-list">
            <?php if (empty($facturas)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🧾</div>
                    <h2>No tienes facturas aún</h2>
                    <p>Las facturas se generan automáticamente al realizar un pago</p>
                    <br>
                    <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-ver" style="margin-top: 20px;">Ver Menú</a>
                </div>
            <?php else: ?>
                <?php foreach ($facturas as $factura): ?>
                    <div class="factura-card">
                        <div class="factura-header">
                            <span class="numero-factura"><?= htmlspecialchars($factura['numero_factura']) ?></span>
                            <span style="background-color: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                ✓ Emitida
                            </span>
                        </div>

                        <div class="factura-info">
                            <div class="info-item">
                                <span class="info-label">Pedido</span>
                                <span class="info-value">#<?= $factura['id_pedido'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Emisión</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Método de Pago</span>
                                <span class="info-value"><?= $metodos[$factura['metodo_pago']] ?? $factura['metodo_pago'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="monto-grande">$<?= number_format($factura['total'], 2) ?></span>
                            </div>
                        </div>

                        <div class="factura-actions">
                            <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=ver&id=<?= $factura['id_factura'] ?>" 
                               class="btn-ver">
                                 Ver Detalle
                            </a>
                            <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=descargar&id=<?= $factura['id_factura'] ?>" 
                               class="btn-descargar"
                               target="_blank">
                                 Descargar/Imprimir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-ocultar alertas
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    </script>
</body>
</html>