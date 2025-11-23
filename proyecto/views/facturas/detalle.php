<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
checkAuth();

$factura = $_SESSION['factura_actual'] ?? null;
$detalles = $_SESSION['detalles_factura'] ?? [];

if (!$factura) {
    header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
    exit;
}

// Limpiar las variables de sesión
unset($_SESSION['factura_actual']);
unset($_SESSION['detalles_factura']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($factura['numero_factura']) ?></title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/facturas.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🧾 Detalle de Factura</h1>
            <a href="/Proyecto_aula/proyecto/views/facturas/index.php" class="btn">← Volver a Facturas</a>
        </header>

        <div class="factura-detalle">
            <!-- Header de la factura -->
            <div class="factura-header-detalle">
                <div class="empresa-info">
                    <h2>🍽️ Restaurante Gourmet</h2>
                    <p>Calle Principal #123</p>
                    <p>Tel: (123) 456-7890</p>
                    <p>Email: info@restaurante.com</p>
                    <p>NIT: 900.123.456-7</p>
                </div>
                <div class="factura-numero-detalle">
                    <h3><?= htmlspecialchars($factura['numero_factura']) ?></h3>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?></p>
                    <p><strong>Pedido:</strong> #<?= $factura['id_pedido'] ?></p>
                </div>
            </div>

            <!-- Datos del cliente -->
            <div class="cliente-datos">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($factura['nombre_usuario']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($factura['correo']) ?></p>
                <p><strong>Método de Pago:</strong> <?= ucfirst($factura['metodo_pago']) ?></p>
            </div>

            <!-- Productos -->
            <div class="productos-factura">
                <h3>Detalle de Productos</h3>
                <table class="tabla-factura">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?= htmlspecialchars($detalle['nombre_producto']) ?></td>
                                <td><?= $detalle['cantidad'] ?></td>
                                <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                <td>$<?= number_format($detalle['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div class="totales-factura">
                <div class="total-linea">
                    <span>Subtotal:</span>
                    <strong>$<?= number_format($factura['subtotal'], 2) ?></strong>
                </div>
                <div class="total-linea">
                    <span>IVA (19%):</span>
                    <strong>$<?= number_format($factura['iva'], 2) ?></strong>
                </div>
                <div class="total-final">
                    <span>Total:</span>
                    <span>$<?= number_format($factura['total'], 2) ?></span>
                </div>
            </div>

            <!-- Notas -->
            <?php if (!empty($factura['notas'])): ?>
                <div class="notas-factura">
                    <h4>Notas</h4>
                    <p><?= nl2br(htmlspecialchars($factura['notas'])) ?></p>
                </div>
            <?php endif; ?>

            <!-- Botones de acción -->
            <div class="acciones-factura">
                <a href="/Proyecto_aula/proyecto/controllers/FacturaController.php?action=descargar&id=<?= $factura['id_factura'] ?>" 
                   class="btn-descargar"
                   target="_blank">
                    📥 Descargar/Imprimir
                </a>
                <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $factura['id_pedido'] ?>" 
                   class="btn-ver">
                    📋 Ver Pedido
                </a>
                <a href="/Proyecto_aula/proyecto/views/menu/index.php" 
                   class="btn"
                   style="background-color: #34495e; color: white;">
                    🍽️ Volver al Menú
                </a>
            </div>
        </div>
    </div>
</body>
</html>