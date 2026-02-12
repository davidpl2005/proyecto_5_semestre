<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Carrito.php';
checkAuth();

$carritoModel = new Carrito();
$id_usuario = $_SESSION['user']['id'];

// Obtener carrito desde la base de datos
$carrito = $carritoModel->obtenerPorUsuario($id_usuario);

$subtotal = 0;

// Calcular el subtotal (suma de todos los subtotales)
foreach ($carrito as $item) {
    $subtotal += $item['subtotal'];
}

// Calcular IVA (19%)
$iva = $subtotal * 0.19;

// Calcular total (subtotal + IVA)
$total = $subtotal + $iva;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/carrito.css">
</head>
<body>
    <div class="carrito-container">
        <header class="carrito-header">
            <h1>🛒 Mi Carrito de Compras</h1>
            <div class="header-actions">
                <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-volver">← Seguir Comprando</a>
                <span class="user-info">Usuario: <?= htmlspecialchars($_SESSION['user']['nombre']) ?></span>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="carrito-content">
            <?php if (empty($carrito)): ?>
                <!-- Carrito vacío -->
                <div class="carrito-vacio">
                    <div class="empty-icon">🛒</div>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Agrega algunos productos deliciosos a tu carrito!</p>
                    <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-primary">Ver Menú</a>
                </div>
            <?php else: ?>
                <!-- Tabla de productos -->
                <div class="carrito-items">
                    <table class="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito as $item): ?>
                                <tr>
                                    <td class="producto-info">
                                        <div class="producto-detalle">
                                            <?php if (!empty($item['imagen'])): ?>
                                                <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($item['imagen']) ?>" 
                                                     alt="<?= htmlspecialchars($item['nombre']) ?>"
                                                     class="producto-img">
                                            <?php else: ?>
                                                <div class="producto-img-placeholder">📦</div>
                                            <?php endif; ?>
                                            <span class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></span>
                                        </div>
                                    </td>
                                    <td class="precio">$<?= number_format($item['precio'], 2) ?></td>
                                    <td class="cantidad">
                                        <form method="post" 
                                              action="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=update" 
                                              class="form-cantidad">
                                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                                            <div class="cantidad-controls">
                                                <button type="button" 
                                                        class="btn-cantidad" 
                                                        onclick="decrementar(this)">-</button>
                                                <input type="number" 
                                                       name="cantidad" 
                                                       value="<?= $item['cantidad'] ?>" 
                                                       min="1" 
                                                       max="99"
                                                       class="input-cantidad"
                                                       onchange="this.form.submit()">
                                                <button type="button" 
                                                        class="btn-cantidad" 
                                                        onclick="incrementar(this)">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="subtotal">$<?= number_format($item['subtotal'], 2) ?></td>
                                    <td class="acciones">
                                        <a href="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=remove&id=<?= $item['id_producto'] ?>" 
                                           class="btn-eliminar"
                                           onclick="return confirm('¿Eliminar este producto del carrito?')">
                                            🗑️ Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen del pedido -->
                <div class="carrito-resumen">
                    <div class="resumen-card">
                        <h3>Resumen del Pedido</h3>
                        
                        <div class="resumen-linea">
                            <span>Subtotal:</span>
                            <span class="monto">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        
                        <div class="resumen-linea">
                            <span>IVA (19%):</span>
                            <span class="monto">$<?= number_format($iva, 2) ?></span>
                        </div>
                        
                        <div class="resumen-divider"></div>
                        
                        <div class="resumen-total">
                            <span>Total a Pagar:</span>
                            <span class="monto-total">$<?= number_format($total, 2) ?></span>
                        </div>

                        <div class="resumen-acciones">
                            <a href="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=clear" 
                               class="btn-vaciar"
                               onclick="return confirm('¿Estás seguro de vaciar todo el carrito?')">
                                Vaciar Carrito
                            </a>
                            <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=checkout" 
                               class="btn-finalizar">
                                Finalizar Pedido 🚀
                            </a>
                        </div>

                        <div class="info-adicional">
                            <p>✓ Pago seguro</p>
                            <p>✓ Envío rápido</p>
                            <p>✓ Garantía de calidad</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Incrementar cantidad
        function incrementar(btn) {
            const input = btn.parentElement.querySelector('.input-cantidad');
            const valorActual = parseInt(input.value);
            if (valorActual < 99) {
                input.value = valorActual + 1;
                input.form.submit();
            }
        }

        // Decrementar cantidad
        function decrementar(btn) {
            const input = btn.parentElement.querySelector('.input-cantidad');
            const valorActual = parseInt(input.value);
            if (valorActual > 1) {
                input.value = valorActual - 1;
                input.form.submit();
            }
        }

        // Auto-ocultar alertas después de 3 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    </script>
</body>
</html>