<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
checkAuth();

$pedido = $_SESSION['pedido_a_pagar'] ?? null;

if (!$pedido) {
    header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
    exit;
}

// Limpiar la variable de sesión
unset($_SESSION['pedido_a_pagar']);

// Calcular subtotal (sin IVA)
$subtotal = $pedido['total'] / 1.19;
$iva = $pedido['total'] - $subtotal;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pagos.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Procesar Pago</h1>
            <p>Pedido #<?= $pedido['id_pedido'] ?></p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="pago-card">
            <!-- Resumen del pedido -->
            <div class="resumen-pedido">
                <h3>📋 Resumen del Pedido</h3>
                <div class="resumen-linea">
                    <span>Subtotal:</span>
                    <span><strong>$<?= number_format($subtotal, 2) ?></strong></span>
                </div>
                <div class="resumen-linea">
                    <span>IVA (19%):</span>
                    <span><strong>$<?= number_format($iva, 2) ?></strong></span>
                </div>
                <div class="resumen-total">
                    <span>Total a Pagar:</span>
                    <span>$<?= number_format($pedido['total'], 2) ?></span>
                </div>
            </div>

            <!-- Formulario de pago -->
            <form method="post" action="/Proyecto_aula/proyecto/controllers/PagoController.php?action=procesar_pago" id="formPago">
                <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                <input type="hidden" name="monto" value="<?= $pedido['total'] ?>">
                
                <div class="metodos-pago">
                    <h3>Selecciona tu método de pago</h3>

                    <!-- Efectivo -->
                    <label class="metodo-opcion" onclick="seleccionarMetodo('efectivo', this)">
                        <input type="radio" name="metodo_pago" value="efectivo" required>
                        <div class="metodo-icon">💵</div>
                        <div class="metodo-info">
                            <div class="metodo-nombre">Efectivo</div>
                            <div class="metodo-descripcion">Paga en efectivo para poder recibir tu pedido</div>
                        </div>
                    </label>

                    <!-- Tarjeta -->
                    <label class="metodo-opcion" onclick="seleccionarMetodo('tarjeta', this)">
                        <input type="radio" name="metodo_pago" value="tarjeta" required>
                        <div class="metodo-icon">💳</div>
                        <div class="metodo-info">
                            <div class="metodo-nombre">Tarjeta de Crédito/Débito</div>
                            <div class="metodo-descripcion">Pago con tarjeta </div>
                        </div>
                    </label>

                    <!-- Transferencia -->
                    <label class="metodo-opcion" onclick="seleccionarMetodo('transferencia', this)">
                        <input type="radio" name="metodo_pago" value="transferencia" required>
                        <div class="metodo-icon">🏦</div>
                        <div class="metodo-info">
                            <div class="metodo-nombre">Transferencia Bancaria</div>
                            <div class="metodo-descripcion">Transferencia a nuestra cuenta bancaria</div>
                        </div>
                    </label>
                </div>

                <div class="botones-pago">
                    <button type="submit" class="btn-pagar" id="btnPagar" disabled>
                        🔒 Confirmar Pago
                    </button>
                    <a href="/Proyecto_aula/proyecto/views/carrito/index.php" class="btn-cancelar">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function seleccionarMetodo(metodo, elemento) {
            // Remover selección previa
            document.querySelectorAll('.metodo-opcion').forEach(opcion => {
                opcion.classList.remove('selected');
            });

            // Agregar selección
            elemento.classList.add('selected');

            // Habilitar botón de pagar
            const btnPagar = document.getElementById('btnPagar');
            btnPagar.disabled = false;
            btnPagar.textContent = '✓ Confirmar Pago';
        }

        // Prevenir envío múltiple
        document.getElementById('formPago').addEventListener('submit', function(e) {
            const btnPagar = document.getElementById('btnPagar');
            btnPagar.disabled = true;
            btnPagar.textContent = 'Procesando...';
        });

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