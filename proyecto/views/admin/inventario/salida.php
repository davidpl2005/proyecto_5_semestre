<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Inventario.php';
checkAdmin();

$inventarioModel = new Inventario();
$inventario = $inventarioModel->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Salida - Inventario</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>➖ Registrar Salida de Inventario</h1>
            <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" class="btn-logout" style="background-color: #34495e;">← Volver</a>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-inventario">
            <h2>Registrar nueva salida</h2>
            <p style="color: #7f8c8d; margin-bottom: 25px;">Las salidas disminuyen el stock disponible (daños, regalos, desperdicios, etc.)</p>

            <form method="post" action="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=procesar_salida" id="formSalida">
                <div class="form-group-inventario">
                    <label for="id_producto">Producto *</label>
                    <select name="id_producto" id="id_producto" required onchange="actualizarInfoProducto()">
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach ($inventario as $item): ?>
                            <option value="<?= $item['id_producto'] ?>" 
                                    data-stock="<?= $item['cantidad'] ?>"
                                    data-nombre="<?= htmlspecialchars($item['nombre_producto']) ?>"
                                    <?= $item['cantidad'] == 0 ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($item['nombre_producto']) ?> 
                                (Stock: <?= $item['cantidad'] ?><?= $item['cantidad'] == 0 ? ' - SIN STOCK' : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="infoProducto" class="info-producto" style="display: none;">
                    <p><strong>Stock actual:</strong> <span id="stockActual">0</span></p>
                    <p><strong>Nuevo stock será:</strong> <span id="nuevoStock" style="color: #e74c3c; font-weight: 700;">0</span></p>
                    <p id="alertaStock" style="display: none; color: #e74c3c; font-weight: 600; margin-top: 10px;">
                        ⚠️ No hay suficiente stock disponible
                    </p>
                </div>

                <div class="form-group-inventario">
                    <label for="cantidad">Cantidad a descontar *</label>
                    <input type="number" 
                           name="cantidad" 
                           id="cantidad" 
                           min="1" 
                           value="1" 
                           required
                           onchange="calcularNuevoStock()">
                </div>

                <div class="form-group-inventario">
                    <label for="descripcion">Descripción / Motivo *</label>
                    <textarea name="descripcion" 
                              id="descripcion" 
                              placeholder="Ej: Productos dañados, Regalo promocional, Desperdicio, etc."
                              required></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-salida" style="flex: 1;" id="btnSubmit">
                        ✓ Registrar Salida
                    </button>
                    <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" 
                       class="btn-entrada" 
                       style="flex: 1; text-align: center; padding: 15px; text-decoration: none; background-color: #7f8c8d;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function actualizarInfoProducto() {
            const select = document.getElementById('id_producto');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('infoProducto');

            if (option.value) {
                const stockActual = parseInt(option.dataset.stock);

                document.getElementById('stockActual').textContent = stockActual;
                document.getElementById('cantidad').max = stockActual;
                
                infoDiv.style.display = 'block';
                calcularNuevoStock();
            } else {
                infoDiv.style.display = 'none';
            }
        }

        function calcularNuevoStock() {
            const select = document.getElementById('id_producto');
            const option = select.options[select.selectedIndex];
            const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
            const btnSubmit = document.getElementById('btnSubmit');
            const alertaStock = document.getElementById('alertaStock');

            if (option.value) {
                const stockActual = parseInt(option.dataset.stock);
                const nuevoStock = stockActual - cantidad;
                document.getElementById('nuevoStock').textContent = nuevoStock;

                if (nuevoStock < 0) {
                    alertaStock.style.display = 'block';
                    btnSubmit.disabled = true;
                    btnSubmit.style.opacity = '0.5';
                    btnSubmit.style.cursor = 'not-allowed';
                } else {
                    alertaStock.style.display = 'none';
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = '1';
                    btnSubmit.style.cursor = 'pointer';
                }
            }
        }

        // Prevenir envío múltiple
        document.getElementById('formSalida').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (!btn.disabled) {
                btn.disabled = true;
                btn.textContent = 'Procesando...';
            } else {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>