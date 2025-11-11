<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Pedido.php';
checkAdmin();

$pedidoModel = new Pedido();
$pedidos = $pedidoModel->getAll();
$estadisticas = $pedidoModel->getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Gestión de Pedidos</h1>
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

        <!-- Estadísticas -->
        <div class="stats-mini">
            <div class="stat-mini">
                <h4>Total Pedidos</h4>
                <p><?= $estadisticas['total_pedidos'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Pendientes</h4>
                <p style="color: #f39c12;"><?= $estadisticas['pendientes'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>En Preparación</h4>
                <p style="color: #3498db;"><?= $estadisticas['preparando'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Listos</h4>
                <p style="color: #9b59b6;"><?= $estadisticas['listos'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Entregados</h4>
                <p style="color: #27ae60;"><?= $estadisticas['entregados'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Ventas Totales</h4>
                <p style="color: #27ae60;">$<?= number_format($estadisticas['ventas_totales'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="6" class="table-empty">
                            No hay pedidos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>
                                <span class="pedido-id">#<?= $pedido['id_pedido'] ?></span>
                            </td>
                            <td>
                                <div class="cliente-info">
                                    <span class="cliente-nombre"><?= htmlspecialchars($pedido['nombre_usuario']) ?></span>
                                    <span class="cliente-email"><?= htmlspecialchars($pedido['correo']) ?></span>
                                </div>
                            </td>
                            <td class="fecha-tabla">
                                <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                            </td>
                            <td>
                                <span class="precio-tabla">$<?= number_format($pedido['total'], 2) ?></span>
                            </td>
                            <td>
                                <form method="post" 
                                      action="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=updateStatus" 
                                      style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $pedido['id_pedido'] ?>">
                                    <select name="estado" class="estado-select" onchange="this.form.submit()">
                                        <option value="pendiente" <?= $pedido['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="preparando" <?= $pedido['estado'] == 'preparando' ? 'selected' : '' ?>>Preparando</option>
                                        <option value="listo" <?= $pedido['estado'] == 'listo' ? 'selected' : '' ?>>Listo</option>
                                        <option value="entregado" <?= $pedido['estado'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                        <option value="cancelado" <?= $pedido['estado'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pedido['id_pedido'] ?>" 
                                   class="btn-ver-admin">
                                    Ver
                                </a>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=delete&id=<?= $pedido['id_pedido'] ?>" 
                                   class="btn-eliminar-admin"
                                   onclick="return confirm('¿Estás seguro de eliminar este pedido?')">
                                    Eliminar
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