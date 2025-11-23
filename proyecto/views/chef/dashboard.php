<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Pedido.php';
checkChef();

$pedidoModel = new Pedido();
$pedidos = $pedidoModel->getAll();
$estadisticas = $pedidoModel->getEstadisticas();

// Filtrar pedidos por estado si se solicita
$filtro_estado = $_GET['estado'] ?? 'todos';
if ($filtro_estado !== 'todos') {
    $pedidos = array_filter($pedidos, function($pedido) use ($filtro_estado) {
        return $pedido['estado'] === $filtro_estado;
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Chef - Cocina</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/chef.css">
</head>
<body>
    <div class="chef-container">
        <!-- Header del Chef -->
        <header class="chef-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>👨‍🍳 Panel de Cocina</h1>
                    <p>Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre']) ?></p>
                </div>
                <a href="/Proyecto_aula/proyecto/controllers/AuthController.php?action=logout" class="chef-logout">
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <!-- Alertas -->
        <?php if (isset($_SESSION['success'])): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #d4edda; color: #155724; border-left: 4px solid #28a745;">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="chef-stats">
            <div class="chef-stat-card">
                <h3>🔴 Pendientes</h3>
                <p style="color: #e74c3c;"><?= $estadisticas['pendientes'] ?? 0 ?></p>
            </div>
            <div class="chef-stat-card">
                <h3>🔵 Preparando</h3>
                <p style="color: #3498db;"><?= $estadisticas['preparando'] ?? 0 ?></p>
            </div>
            <div class="chef-stat-card">
                <h3>🟢 Listos</h3>
                <p style="color: #9b59b6;"><?= $estadisticas['listos'] ?? 0 ?></p>
            </div>
            <div class="chef-stat-card">
                <h3>✅ Entregados Hoy</h3>
                <p style="color: #27ae60;"><?= $estadisticas['entregados'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Filtros de estado -->
        <div class="estado-filtros">
            <h3>Filtrar por Estado:</h3>
            <div class="filtro-botones">
                <a href="?estado=todos" class="filtro-btn <?= $filtro_estado === 'todos' ? 'active' : '' ?>">
                    Todos (<?= count($pedidoModel->getAll()) ?>)
                </a>
                <a href="?estado=pendiente" class="filtro-btn <?= $filtro_estado === 'pendiente' ? 'active' : '' ?>">
                    🔴 Pendientes (<?= $estadisticas['pendientes'] ?? 0 ?>)
                </a>
                <a href="?estado=preparando" class="filtro-btn <?= $filtro_estado === 'preparando' ? 'active' : '' ?>">
                    🔵 Preparando (<?= $estadisticas['preparando'] ?? 0 ?>)
                </a>
                <a href="?estado=listo" class="filtro-btn <?= $filtro_estado === 'listo' ? 'active' : '' ?>">
                    🟢 Listos (<?= $estadisticas['listos'] ?? 0 ?>)
                </a>
                <a href="?estado=entregado" class="filtro-btn <?= $filtro_estado === 'entregado' ? 'active' : '' ?>">
                    ✅ Entregados (<?= $estadisticas['entregados'] ?? 0 ?>)
                </a>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <table class="chef-pedidos-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado Actual</th>
                    <th>Cambiar Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            <?php if ($filtro_estado !== 'todos'): ?>
                                No hay pedidos con estado "<?= ucfirst($filtro_estado) ?>"
                            <?php else: ?>
                                No hay pedidos registrados
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                        // CORREGIR: Calcular tiempo transcurrido con la zona horaria correcta
                        // Establecer zona horaria de Colombia
                        date_default_timezone_set('America/Bogota');
                        
                        $fecha_pedido = strtotime($pedido['fecha_pedido']);
                        $ahora = time();
                        $diferencia_segundos = $ahora - $fecha_pedido;
                        $minutos = floor($diferencia_segundos / 60);
                        
                        // Determinar prioridad
                        if ($minutos > 30 && $pedido['estado'] === 'pendiente') {
                            $prioridad = 'alta';
                            $prioridad_texto = '🔴 URGENTE';
                        } elseif ($minutos > 15 && $pedido['estado'] === 'pendiente') {
                            $prioridad = 'media';
                            $prioridad_texto = '🟡 Atención';
                        } else {
                            $prioridad = 'baja';
                            $prioridad_texto = '🟢 Normal';
                        }

                        // Contar items del pedido
                        require_once __DIR__ . '/../../models/DetallePedido.php';
                        $detalleModel = new DetallePedido();
                        $detalles = $detalleModel->getByPedido($pedido['id_pedido']);
                        $total_items = 0;
                        foreach ($detalles as $detalle) {
                            $total_items += $detalle['cantidad'];
                        }
                        ?>
                        <tr>
                            <td>
                                <div>
                                    <strong style="font-size: 18px; color: #e67e22;">#<?= $pedido['id_pedido'] ?></strong>
                                    <?php if ($pedido['estado'] === 'pendiente' && $minutos > 15): ?>
                                        <br>
                                        <span class="prioridad-badge prioridad-<?= $prioridad ?>">
                                            <?= $prioridad_texto ?>
                                        </span>
                                    <?php endif; ?>
                                    <div class="tiempo-pedido <?= $minutos > 30 ? 'tiempo-urgente' : '' ?>">
                                        <?php if ($minutos < 1): ?>
                                            Hace menos de 1 min
                                        <?php elseif ($minutos < 60): ?>
                                            Hace <?= $minutos ?> min
                                        <?php else: ?>
                                            Hace <?= floor($minutos / 60) ?>h <?= $minutos % 60 ?>m
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50;">
                                    <?= htmlspecialchars($pedido['nombre_usuario']) ?>
                                </div>
                                <div style="font-size: 12px; color: #7f8c8d;">
                                    <?= htmlspecialchars($pedido['correo']) ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                            <td>
                                <span class="items-badge"><?= $total_items ?> items</span>
                            </td>
                            <td>
                                <strong style="color: #27ae60; font-size: 16px;">
                                    $<?= number_format($pedido['total'], 2) ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $estados_badge = [
                                    'pendiente' => ['color' => '#f39c12', 'icono' => '🔴', 'texto' => 'Pendiente'],
                                    'preparando' => ['color' => '#3498db', 'icono' => '🔵', 'texto' => 'Preparando'],
                                    'listo' => ['color' => '#9b59b6', 'icono' => '🟢', 'texto' => 'Listo'],
                                    'entregado' => ['color' => '#27ae60', 'icono' => '✅', 'texto' => 'Entregado'],
                                    'cancelado' => ['color' => '#e74c3c', 'icono' => '❌', 'texto' => 'Cancelado']
                                ];
                                $badge = $estados_badge[$pedido['estado']];
                                ?>
                                <span style="background-color: <?= $badge['color'] ?>20; color: <?= $badge['color'] ?>; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;">
                                    <?= $badge['icono'] ?> <?= $badge['texto'] ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" 
                                      action="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=updateEstado" 
                                      style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $pedido['id_pedido'] ?>">
                                    <select name="estado" class="chef-estado-select" onchange="this.form.submit()">
                                        <option value="pendiente" <?= $pedido['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                            🔴 Pendiente
                                        </option>
                                        <option value="preparando" <?= $pedido['estado'] == 'preparando' ? 'selected' : '' ?>>
                                            🔵 Preparando
                                        </option>
                                        <option value="listo" <?= $pedido['estado'] == 'listo' ? 'selected' : '' ?>>
                                            🟢 Listo
                                        </option>
                                        <option value="entregado" <?= $pedido['estado'] == 'entregado' ? 'selected' : '' ?>>
                                            ✅ Entregado
                                        </option>
                                        <option value="cancelado" <?= $pedido['estado'] == 'cancelado' ? 'selected' : '' ?>>
                                            ❌ Cancelado
                                        </option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $pedido['id_pedido'] ?>" 
                                   class="chef-btn-accion">
                                    👁️ Ver Detalle
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
            const alerts = document.querySelectorAll('[style*="background-color: #d4edda"], [style*="background-color: #f8d7da"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);

        // Auto-refresh cada 30 segundos para actualizar tiempos y nuevos pedidos
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>