<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Factura.php';
checkAdmin();

$facturaModel = new Factura();
$facturas = $facturaModel->getAll();
$estadisticas = $facturaModel->getEstadisticas();

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
    <title>Gestión de Facturas - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>🧾 Gestión de Facturas</h1>
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

        <!-- Estadísticas de facturas -->
        <div class="stats-mini">
            <div class="stat-mini">
                <h4>Total Facturas</h4>
                <p><?= $estadisticas['total_facturas'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Subtotal General</h4>
                <p style="color: #3498db;">$<?= number_format($estadisticas['total_subtotal'] ?? 0, 2) ?></p>
            </div>
            <div class="stat-mini">
                <h4>IVA Total</h4>
                <p style="color: #f39c12;">$<?= number_format($estadisticas['total_iva'] ?? 0, 2) ?></p>
            </div>
            <div class="stat-mini">
                <h4>💰 Total Facturado</h4>
                <p style="color: #27ae60; font-size: 20px;">$<?= number_format($estadisticas['total_facturado'] ?? 0, 2) ?></p>
            </div>
            <div class="stat-mini">
                <h4>Promedio por Factura</h4>
                <p style="color: #9b59b6;">$<?= number_format($estadisticas['promedio_factura'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Tabla de facturas -->
        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Pedido</th>
                    <th>Método Pago</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($facturas)): ?>
                    <tr>
                        <td colspan="9" class="table-empty">
                            No hay facturas registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($facturas as $factura): ?>
                        <tr>
                            <td>
                                <span class="pedido-id"><?= htmlspecialchars($factura['numero_factura']) ?></span>
                            </td>
                            <td>
                                <div class="cliente-info">
                                    <span class="cliente-nombre"><?= htmlspecialchars($factura['nombre_usuario']) ?></span>
                                    <span class="cliente-email"><?= htmlspecialchars($factura['correo']) ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $factura['id_pedido'] ?>" 
                                   style="color: #667eea; font-weight: 600; text-decoration: none;">
                                    #<?= $factura['id_pedido'] ?>
                                </a>
                            </td>
                            <td><?= $metodos[$factura['metodo_pago']] ?? $factura['metodo_pago'] ?></td>
                            <td>$<?= number_format($factura['subtotal'], 2) ?></td>
                            <td style="color: #f39c12; font-weight: 600;">$<?= number_format($factura['iva'], 2) ?></td>
                            <td>
                                <span class="precio-tabla">$<?= number_format($factura['total'], 2) ?></span>
                            </td>
                            <td class="fecha-tabla">
                                <?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=ver&id=<?= $factura['id_factura'] ?>" 
                                   class="btn-ver-admin"
                                   title="Ver factura">
                                    Ver
                                </a>
                                <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=descargar&id=<?= $factura['id_factura'] ?>" 
                                   class="btn-ver-admin"
                                   style="background-color: #27ae60;"
                                   title="Descargar factura"
                                   target="_blank">
                                    📥
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