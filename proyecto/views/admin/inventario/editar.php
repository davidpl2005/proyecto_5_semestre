<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
checkAdmin();

$inventario = $_SESSION['inventario_editar'] ?? null;

if (!$inventario) {
    header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
    exit;
}

unset($_SESSION['inventario_editar']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Stock - <?= htmlspecialchars($inventario['nombre_producto']) ?></title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Configurar Stocks</h1>
            <br>
            <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" class="btn-logout" style="background-color: #34495e;">← Volver</a>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-inventario">
            <h2><?= htmlspecialchars($inventario['nombre_producto']) ?></h2>
            <p style="color: #7f8c8d; margin-bottom: 25px;">Configura los niveles de stock mínimo y máximo para este producto</p>

            <div class="info-producto" style="margin-bottom: 25px;">
                <p><strong>Stock actual:</strong> 
                    <span style="font-size: 24px; color: <?= $inventario['cantidad'] == 0 ? '#e74c3c' : ($inventario['cantidad'] <= $inventario['stock_minimo'] ? '#f39c12' : '#27ae60') ?>">
                        <?= $inventario['cantidad'] ?>
                    </span>
                </p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($inventario['categoria'] ?? 'Sin categoría') ?></p>
                <?php if (!empty($inventario['imagen'])): ?>
                    <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($inventario['imagen']) ?>" 
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 10px;"
                         alt="<?= htmlspecialchars($inventario['nombre_producto']) ?>">
                <?php endif; ?>
            </div>

            <form method="post" action="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=actualizar">
                <input type="hidden" name="id_producto" value="<?= $inventario['id_producto'] ?>">

                <div class="form-group-inventario">
                    <label for="stock_minimo">Stock Mínimo *</label>
                    <input type="number" 
                           name="stock_minimo" 
                           id="stock_minimo" 
                           min="0" 
                           value="<?= $inventario['stock_minimo'] ?>" 
                           required
                           onchange="validarStocks()">
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                        Nivel de stock que activa la alerta de reposición
                    </small>
                </div>

                <div class="form-group-inventario">
                    <label for="stock_maximo">Stock Máximo *</label>
                    <input type="number" 
                           name="stock_maximo" 
                           id="stock_maximo" 
                           min="1" 
                           value="<?= $inventario['stock_maximo'] ?>" 
                           required
                           onchange="validarStocks()">
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                        Capacidad máxima de almacenamiento para este producto
                    </small>
                </div>

                <div id="errorStocks" style="display: none; background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                    El stock mínimo debe ser menor que el stock máximo
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-entrada" style="flex: 1;" id="btnGuardar">
                        ✓ Guardar Cambios
                    </button>
                    <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" 
                       class="btn-salida" 
                       style="flex: 1; text-align: center; padding: 15px; text-decoration: none; background-color: #7f8c8d;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarStocks() {
            const minimo = parseInt(document.getElementById('stock_minimo').value);
            const maximo = parseInt(document.getElementById('stock_maximo').value);
            const errorDiv = document.getElementById('errorStocks');
            const btnGuardar = document.getElementById('btnGuardar');

            if (minimo >= maximo) {
                errorDiv.style.display = 'block';
                btnGuardar.disabled = true;
                btnGuardar.style.opacity = '0.5';
                btnGuardar.style.cursor = 'not-allowed';
            } else {
                errorDiv.style.display = 'none';
                btnGuardar.disabled = false;
                btnGuardar.style.opacity = '1';
                btnGuardar.style.cursor = 'pointer';
            }
        }

        // Validar al cargar
        validarStocks();
    </script>
</body>
</html>