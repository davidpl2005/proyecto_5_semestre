<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Pago.php';
checkAuth();

$pagoModel = new Pago();
$pagos = $pagoModel->getByUsuario($_SESSION['user']['id']);

// Mapeo de métodos de pago
$metodos = [
    'efectivo' => '💵 Efectivo',
    'tarjeta' => '💳 Tarjeta',
    'transferencia' => '🏦 Transferencia'
];

// Mapeo de estados
$estados = [
    'pendiente' => 'Pendiente',
    'completado' => 'Completado',
    'cancelado' => 'Cancelado'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pedidos.css">
    <style>
        .pago-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            background: white;
        }

        .pago-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .pago-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .referencia {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
        }

        .estado-completado {
            background-color: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .estado-pendiente {
            background-color: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .estado-cancelado {
            background-color: #f8d7da;
            color: #721c24;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .pago-detalles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .monto-grande {
            font-size: 24px;
            font-weight: 700;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>💳 Mis Pagos</h1>
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
            <?php if (empty($pagos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💳</div>
                    <h2>No tienes pagos registrados</h2>
                    <p>Cuando realices un pago, aparecerá aquí</p>
                    <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-ver" style="margin-top: 20px;">Ver Menú</a>
                </div>
            <?php else: ?>
                <?php foreach ($pagos as $pago): ?>
                    <div class="pago-card">
                        <div class="pago-header">
                            <span class="referencia"><?= htmlspecialchars($pago['referencia']) ?></span>
                            <span class="estado-<?= $pago['estado_pago'] ?>">
                                <?= $estados[$pago['estado_pago']] ?? $pago['estado_pago'] ?>
                            </span>
                        </div>

                        <div class="pago-detalles">
                            <div class="info-item">
                                <span class="info-label">Pedido</span>
                                <span class="info-value">#<?= $pago['id_pedido'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Método de Pago</span>
                                <span class="info-value"><?= $metodos[$pago['metodo_pago']] ?? $pago['metodo_pago'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Monto</span>
                                <span class="monto-grande">$<?= number_format($pago['monto'], 2) ?></span>
                            </div>
                        </div>

                        <div class="pedido-actions">
                            <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pago['id_pedido'] ?>" 
                               class="btn-ver">
                                📋 Ver Pedido
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