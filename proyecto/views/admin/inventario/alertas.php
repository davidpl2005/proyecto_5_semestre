<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Inventario.php';
checkAdmin();

$inventarioModel = new Inventario();
$productos_bajo_stock = $inventarioModel->getStockBajo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Stock - Inventario</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>⚠️ Alertas de Stock Bajo</h1>
            <br>
            <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" class="btn-logout" style="background-color: #34495e;">← Volver al Inventario</a>
        </header>

        <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <?php if (empty($productos_bajo_stock)): ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 80px; margin-bottom: 20px;">✅</div>
                    <h2 style="color: #27ae60; margin-bottom: 10px;">¡Todo en orden!</h2>
                    <p style="color: #7f8c8d;">No hay productos con stock bajo en este momento</p>
                    <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" 
                       class="btn-entrada" 
                       style="margin-top: 30px; display: inline-block;">
                        Volver al Inventario
                    </a>
                </div>
            <?php else: ?>
                <div style="background-color: #fff3cd; border-left: 4px solid #f39c12; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Atención Requerida</h3>
                    <p style="color: #856404; margin: 0;">
                        Hay <strong><?= count($productos_bajo_stock) ?></strong> producto(s) con stock bajo o sin stock. 
                        Se recomienda realizar una compra o reposición.
                    </p>
                </div>

                <?php foreach ($productos_bajo_stock as $producto): ?>
                    <?php
                    // Evitar división por cero
                    $porcentaje = ($producto['stock_minimo'] > 0) 
                        ? ($producto['cantidad'] / $producto['stock_minimo']) * 100 
                        : 0;
                    
                    if ($producto['cantidad'] == 0) {
                        $clase_alerta = 'sin-stock';
                        $texto_estado = 'SIN STOCK';
                        $color_barra = '#e74c3c';
                    } elseif ($porcentaje <= 50) {
                        $clase_alerta = 'stock-critico';
                        $texto_estado = 'CRÍTICO';
                        $color_barra = '#e74c3c';
                    } else {
                        $clase_alerta = 'stock-bajo';
                        $texto_estado = 'BAJO';
                        $color_barra = '#f39c12';
                    }
                    ?>
                    <div class="alerta-card">
                        <div class="alerta-header">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($producto['imagen']) ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid #e9ecef;"
                                         alt="<?= htmlspecialchars($producto['nombre_producto']) ?>">
                                <?php endif; ?>
                                <div>
                                    <div class="alerta-producto"><?= htmlspecialchars($producto['nombre_producto']) ?></div>
                                    <span class="badge-stock badge-<?= $producto['cantidad'] == 0 ? 'sin-stock' : ($porcentaje <= 50 ? 'critico' : 'bajo') ?>">
                                        <?= $texto_estado ?>
                                    </span>
                                </div>
                            </div>
                            <div class="alerta-stock"><?= $producto['cantidad'] ?></div>
                        </div>

                        <div style="margin-top: 15px;">
                            <!-- Información de stocks en tarjetas -->
                            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-around; text-align: center;">
                                    <div>
                                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Stock Actual</p>
                                        <p style="font-size: 24px; font-weight: 700; color: <?= $color_barra ?>; margin: 0;">
                                            <?= $producto['cantidad'] ?>
                                        </p>
                                    </div>
                                    <div style="border-left: 2px solid #dee2e6; height: 50px;"></div>
                                    <div>
                                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Stock Mínimo</p>
                                        <p style="font-size: 24px; font-weight: 700; color: #f39c12; margin: 0;">
                                            <?= $producto['stock_minimo'] ?>
                                        </p>
                                    </div>
                                    <div style="border-left: 2px solid #dee2e6; height: 50px;"></div>
                                    <div>
                                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Stock Máximo</p>
                                        <p style="font-size: 24px; font-weight: 700; color: #27ae60; margin: 0;">
                                            <?= $producto['stock_maximo'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <a href="/Proyecto_aula/proyecto/views/admin/inventario/entrada.php" 
                                   class="btn-entrada" 
                                   style="padding: 8px 16px; font-size: 14px;">
                                    ➕ Registrar Entrada
                                </a>
                                <a href="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=editar&id=<?= $producto['id_producto'] ?>" 
                                   class="btn-movimientos" 
                                   style="padding: 8px 16px; font-size: 14px;">
                                    ⚙️ Configurar Stocks
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>