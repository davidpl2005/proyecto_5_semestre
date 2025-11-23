<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/MovimientoInventario.php';
checkAdmin();

$movimientoModel = new MovimientoInventario();
$movimientos = $movimientoModel->getAll();
$estadisticas = $movimientoModel->getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - Inventario</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>📋 Historial de Movimientos</h1>
            <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" class="btn-logout" style="background-color: #34495e;">← Volver al Inventario</a>
        </header>

        <!-- Estadísticas de movimientos -->
        <div class="stats-mini">
            <div class="stat-mini">
                <h4>Total Movimientos</h4>
                <p style="color: #3498db;"><?= $estadisticas['total_movimientos'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Entradas</h4>
                <p style="color: #27ae60;"><?= $estadisticas['num_entradas'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Salidas</h4>
                <p style="color: #e74c3c;"><?= $estadisticas['num_salidas'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Total Unidades Entrada</h4>
                <p style="color: #27ae60;"><?= $estadisticas['total_entradas'] ?? 0 ?></p>
            </div>
            <div class="stat-mini">
                <h4>Total Unidades Salida</h4>
                <p style="color: #e74c3c;"><?= $estadisticas['total_salidas'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Tabla de movimientos -->
        <table class="movimientos-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Descripción</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            No hay movimientos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])) ?></td>
                            <td>
                                <?php if ($mov['tipo_movimiento'] == 'entrada'): ?>
                                    <span class="tipo-entrada">
                                        <span class="icono-entrada">↗️</span> ENTRADA
                                    </span>
                                <?php else: ?>
                                    <span class="tipo-salida">
                                        <span class="icono-salida">↘️</span> SALIDA
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if (!empty($mov['imagen'])): ?>
                                        <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($mov['imagen']) ?>" 
                                             class="producto-img-small"
                                             alt="<?= htmlspecialchars($mov['nombre_producto']) ?>">
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($mov['nombre_producto']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 16px; color: <?= $mov['tipo_movimiento'] == 'entrada' ? '#27ae60' : '#e74c3c' ?>">
                                    <?= $mov['tipo_movimiento'] == 'entrada' ? '+' : '-' ?><?= $mov['cantidad'] ?>
                                </strong>
                            </td>
                            <td style="max-width: 300px;">
                                <?= htmlspecialchars($mov['descripcion']) ?>
                            </td>
                            <td><?= htmlspecialchars($mov['nombre_usuario'] ?? 'Sistema') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>