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
    <title>Registrar Entrada - Inventario</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/inventario.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>➕ Registrar Entrada de Inventario</h1>
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
            <h2>Registrar nueva entrada</h2>
            <p style="color: #7f8c8d; margin-bottom: 25px;">Las entradas aumentan el stock disponible (compras, devoluciones, etc.)</p>

            <form method="post" action="/Proyecto_aula/proyecto/controllers/InventarioController.php?action=procesar_entrada" id="formEntrada">
                <div class="form-group-inventario">
                    <label for="id_producto">Producto *</label>
                    <select name="id_producto" id="id_producto" required onchange="actualizarInfoProducto()">
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach ($inventario as $item): ?>
                            <option value="<?= $item['id_producto'] ?>" 
                                    data-stock="<?= $item['cantidad'] ?>"
                                    data-nombre="<?= htmlspecialchars($item['nombre_producto']) ?>"
                                    data-minimo="<?= $item['stock_minimo'] ?>"
                                    data-maximo="<?= $item['stock_maximo'] ?>">
                                <?= htmlspecialchars($item['nombre_producto']) ?> (Stock actual: <?= $item['cantidad'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="infoProducto" class="info-producto" style="display: none;">
                    <p><strong>Stock actual:</strong> <span id="stockActual">0</span></p>
                    <p><strong>Stock mínimo:</strong> <span id="stockMinimo">0</span></p>
                    <p><strong>Stock máximo:</strong> <span id="stockMaximo">0</span></p>
                    <p><strong>Nuevo stock será:</strong> <span id="nuevoStock" style="color: #27ae60; font-weight: 700;">0</span></p>
                </div>

                <div class="form-group-inventario">
                    <label for="cantidad">Cantidad a ingresar *</label>
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
                              placeholder="Ej: Compra a proveedor XYZ, Reposición de stock, etc."
                              required></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-entrada" style="flex: 1;">
                        ✓ Registrar Entrada
                    </button>
                    <a href="/Proyecto_aula/proyecto/views/admin/inventario/index.php" 
                       class="btn-salida" 
                       style="flex: 1; text-align: center; padding: 15px; text-decoration: none;">
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
                const stockMinimo = parseInt(option.dataset.minimo);
                const stockMaximo = parseInt(option.dataset.maximo);

                document.getElementById('stockActual').textContent = stockActual;
                document.getElementById('stockMinimo').textContent = stockMinimo;
                document.getElementById('stockMaximo').textContent = stockMaximo;
                
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

            if (option.value) {
                const stockActual = parseInt(option.dataset.stock);
                const nuevoStock = stockActual + cantidad;
                document.getElementById('nuevoStock').textContent = nuevoStock;
            }
        }

        // Prevenir envío múltiple
        document.getElementById('formEntrada').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Procesando...';
        });
    </script>
</body>
</html>