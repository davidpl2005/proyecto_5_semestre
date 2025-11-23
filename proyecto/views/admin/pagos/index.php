<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Pago.php';
checkAdmin();

$pagoModel = new Pago();
$pagos = $pagoModel->getAll();
$estadisticas = $pagoModel->getEstadisticas();

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
    <title>Gestión de Pagos - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>💳 Gestión de Pagos</h1>
            <br>
            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="btn-logout" style="background-color: #34495e;">← Volver al Panel</a>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #d4edda; color: #155724;">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Estadísticas de pagos -->
        <div class="stats-mini">
            <div class="stat-mini">
                <h4>Total Pagos</h4>
                <p><?= $estadisticas['total_pagos'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Completados</h4>
                <p style="color: #27ae60;"><?= $estadisticas['completados'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Pendientes</h4>
                <p style="color: #f39c12;"><?= $estadisticas['pendientes'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>💵 Efectivo</h4>
                <p style="color: #2c3e50;"><?= $estadisticas['efectivo'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>💳 Tarjeta</h4>
                <p style="color: #3498db;"><?= $estadisticas['tarjeta'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>🏦 Transferencia</h4>
                <p style="color: #9b59b6;"><?= $estadisticas['transferencia'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>💰 Total Recaudado</h4>
                <p style="color: #27ae60; font-size: 20px;">$<?= number_format($estadisticas['total_recaudado'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Tabla de pagos -->
        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Cliente</th>
                    <th>Pedido</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="8" class="table-empty">
                            No hay pagos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td>
                                <span class="pedido-id"><?= htmlspecialchars($pago['referencia']) ?></span>
                            </td>
                            <td>
                                <div class="cliente-info">
                                    <span class="cliente-nombre"><?= htmlspecialchars($pago['nombre_usuario']) ?></span>
                                    <span class="cliente-email"><?= htmlspecialchars($pago['correo']) ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pago['id_pedido'] ?>" 
                                   style="color: #667eea; font-weight: 600; text-decoration: none;">
                                    #<?= $pago['id_pedido'] ?>
                                </a>
                            </td>
                            <td><?= $metodos[$pago['metodo_pago']] ?? $pago['metodo_pago'] ?></td>
                            <td>
                                <span class="precio-tabla">$<?= number_format($pago['monto'], 2) ?></span>
                            </td>
                            <td>
                                <?php if ($pago['estado_pago'] == 'completado'): ?>
                                    <span style="background-color: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                        ✓ Completado
                                    </span>
                                <?php elseif ($pago['estado_pago'] == 'pendiente'): ?>
                                    <span style="background-color: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                        ⏳ Pendiente
                                    </span>
                                <?php else: ?>
                                    <span style="background-color: #f8d7da; color: #721c24; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                        ✗ Cancelado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fecha-tabla">
                                <?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pago['id_pedido'] ?>" 
                                   class="btn-ver-admin"
                                   title="Ver pedido relacionado">
                                    📋 Ver Pedido
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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