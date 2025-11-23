<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Inventario.php';
checkAdmin();

$inventarioModel = new Inventario();
$inventario = $inventarioModel->getAll();
$estadisticas = $inventarioModel->getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>📦 Gestión de Inventario</h1>
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
        <div class="stats-inventario">
            <div class="stat-inventario">
                <h4>Total Productos</h4>
                <p style="color: #3498db;"><?= $estadisticas['total_productos'] ?? 0 ?></p>
            </div>
            <div class="stat-inventario">
                <h4>Total Unidades</h4>
                <p style="color: #2c3e50;"><?= $estadisticas['total_unidades'] ?? 0 ?></p>
            </div>
            <div class="stat-inventario">
                <h4>Stock OK</h4>
                <p style="color: #27ae60;"><?= $estadisticas['productos_stock_ok'] ?? 0 ?></p>
            </div>
            <div class="stat-inventario">
                <h4>⚠️ Stock Bajo</h4>
                <p style="color: #f39c12;"><?= $estadisticas['productos_bajo_stock'] ?? 0 ?></p>
            </div>
            <div class="stat-inventario">
                <h4>❌ Sin Stock</h4>
                <p style="color: #e74c3c;"><?= $estadisticas['productos_sin_stock'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Acciones -->
        <div class="acciones-inventario">
            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=registrar_entrada" class="btn-entrada">
                ➕ Registrar Entrada
            </a>
            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=registrar_salida" class="btn-salida">
                ➖ Registrar Salida
            </a>
            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=movimientos" class="btn-movimientos">
                📋 Ver Movimientos
            </a>
            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=alertas" class="btn-movimientos" style="background-color: #f39c12;">
                ⚠️ Alertas de Stock
            </a>
            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=inicializar_faltantes" 
               class="btn-movimientos" 
               style="background-color: #9b59b6;"
               onclick="return confirm('¿Crear registros de inventario para productos que no lo tengan?')">
                🔄 Inicializar Faltantes
            </a>
        </div>

        <!-- Tabla de inventario -->
        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Stock Máximo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inventario)): ?>
                    <tr>
                        <td colspan="7" class="table-empty">
                            No hay productos en el inventario. 
                            <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=inicializar_faltantes">
                                Inicializar inventario
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inventario as $item): ?>
                        <?php
                        // Determinar estado del stock
                        if ($item['cantidad'] == 0) {
                            $estado_clase = 'badge-sin-stock';
                            $estado_texto = '❌ Sin Stock';
                        } elseif ($item['cantidad'] <= $item['stock_minimo']) {
                            $estado_clase = 'badge-critico';
                            $estado_texto = '⚠️ Crítico';
                        } elseif ($item['cantidad'] <= ($item['stock_minimo'] * 1.5)) {
                            $estado_clase = 'badge-bajo';
                            $estado_texto = '⚡ Bajo';
                        } else {
                            $estado_clase = 'badge-ok';
                            $estado_texto = '✓ OK';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="producto-inventario">
                                    <?php if (!empty($item['imagen'])): ?>
                                        <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($item['imagen']) ?>" 
                                             class="producto-img-small"
                                             alt="<?= htmlspecialchars($item['nombre_producto']) ?>">
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($item['nombre_producto']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></td>
                            <td>
                                <strong style="font-size: 18px; 
                                    color: <?= $item['cantidad'] == 0 ? '#e74c3c' : ($item['cantidad'] <= $item['stock_minimo'] ? '#f39c12' : '#27ae60') ?>">
                                    <?= $item['cantidad'] ?>
                                </strong>
                            </td>
                            <td><?= $item['stock_minimo'] ?></td>
                            <td><?= $item['stock_maximo'] ?></td>
                            <td>
                                <span class="badge-stock <?= $estado_clase ?>">
                                    <?= $estado_texto ?>
                                </span>
                            </td>
                            <td>
                                <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=editar&id=<?= $item['id_producto'] ?>" 
                                   class="btn-ver-admin"
                                   title="Editar stocks">
                                    ⚙️
                                </a>
                                <button onclick="mostrarModalAjuste(<?= $item['id_producto'] ?>, '<?= htmlspecialchars($item['nombre_producto']) ?>', <?= $item['cantidad'] ?>)" 
                                        class="btn-ajustar"
                                        title="Ajustar cantidad">
                                    🔧
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal simple para ajustar stock -->
    <div id="modalAjuste" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 20px;">Ajustar Stock</h3>
            <form method="post" action="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=ajustar">
                <input type="hidden" name="id_producto" id="ajuste_id_producto">
                <p style="margin-bottom: 15px;">Producto: <strong id="ajuste_nombre_producto"></strong></p>
                <p style="margin-bottom: 15px;">Stock actual: <strong id="ajuste_stock_actual"></strong></p>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nueva cantidad:</label>
                    <input type="number" name="cantidad" min="0" required 
                           style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 6px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Motivo:</label>
                    <input type="text" name="motivo" placeholder="Ej: Inventario físico, corrección, etc." 
                           style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 6px;">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-entrada" style="flex: 1;">Confirmar</button>
                    <button type="button" onclick="cerrarModalAjuste()" class="btn-salida" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mostrarModalAjuste(id, nombre, stockActual) {
            document.getElementById('ajuste_id_producto').value = id;
            document.getElementById('ajuste_nombre_producto').textContent = nombre;
            document.getElementById('ajuste_stock_actual').textContent = stockActual;
            document.getElementById('modalAjuste').style.display = 'flex';
        }

        function cerrarModalAjuste() {
            document.getElementById('modalAjuste').style.display = 'none';
        }

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