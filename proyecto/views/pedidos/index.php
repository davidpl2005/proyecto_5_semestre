<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Pedido.php';
checkAuth();

$pedidoModel = new Pedido();
$pedidos = $pedidoModel->getByUsuario($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pedidos.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📦 Mis Pedidos</h1>
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

        <div class="pedidos-list">
            <?php if (empty($pedidos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h2>No tienes pedidos aún</h2>
                    <p>Realiza tu primer pedido desde nuestro menú</p>
                    <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-ver" style="margin-top: 20px;">Ver Menú</a>
                </div>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <span class="pedido-numero">Pedido #<?= $pedido['id_pedido'] ?></span>
                            <span class="estado-badge estado-<?= $pedido['estado'] ?>">
                                <?= ucfirst($pedido['estado']) ?>
                            </span>
                        </div>

                        <div class="pedido-info">
                            <div class="info-item">
                                <span class="info-label">Fecha</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="info-value" style="color: #27ae60;">$<?= number_format($pedido['total'], 2) ?></span>
                            </div>
                        </div>

                        <div class="pedido-actions">
                            <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pedido['id_pedido'] ?>" 
                               class="btn-ver">
                                👁️ Ver Detalles
                            </a>
                            <?php if ($pedido['estado'] === 'pendiente'): ?>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=cancel&id=<?= $pedido['id_pedido'] ?>" 
                                   class="btn-cancelar"
                                   onclick="return confirm('¿Estás seguro de cancelar este pedido?')">
                                    ❌ Cancelar Pedido
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-ocultar alertas después de 3 segundos
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