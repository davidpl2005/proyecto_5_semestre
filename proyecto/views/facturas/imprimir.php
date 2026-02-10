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

// Determinar la URL de retorno según el rol del usuario
$rol_usuario = $_SESSION['user']['rol'] ?? 'cliente';

switch ($rol_usuario) {
    case 'admin':
        $url_volver = '/Proyecto_aula/proyecto/views/admin/facturas/index.php';
        $texto_volver = '← Volver a Gestión de Facturas';
        break;
    default: // cliente o chef
        $url_volver = '/Proyecto_aula/proyecto/views/facturas/index.php';
        $texto_volver = '← Volver a Mis Facturas';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($factura['numero_factura']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            color: #333;
        }

        .factura-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .empresa h1 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empresa p {
            font-size: 12px;
            margin: 3px 0;
        }

        .factura-info {
            text-align: right;
        }

        .factura-info h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .factura-info p {
            font-size: 12px;
            margin: 3px 0;
        }

        .cliente {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .cliente h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .cliente p {
            font-size: 13px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            border-bottom: 2px solid #333;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .totales {
            width: 300px;
            margin-left: auto;
            border-top: 2px solid #333;
            padding-top: 15px;
        }

        .total-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }

        .notas {
            background-color: #fff9e6;
            padding: 15px;
            border-left: 4px solid #f39c12;
            margin-top: 30px;
        }

        .notas h4 {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .notas p {
            font-size: 12px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #7f8c8d;
        }

        .btn-container {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-imprimir {
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-imprimir:hover {
            background-color: #5568d3;
        }

        .btn-volver {
            background-color: #34495e;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-volver:hover {
            background-color: #2c3e50;
        }

        .btn-panel {
            background-color: #27ae60;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-panel:hover {
            background-color: #229954;
        }

        .admin-badge {
            background-color: #e8f4fd;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            color: #2c5282;
            font-weight: 600;
            font-size: 14px;
        }

        @media print {
            .btn-container,
            .admin-badge {
                display: none;
            }

            body {
                padding: 0;
            }

            .factura-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="btn-container">
        <?php if ($rol_usuario === 'admin'): ?>
            <div class="admin-badge" style="width: 100%; text-align: center;">
                👑 Vista de Administrador
            </div>
        <?php endif; ?>
        
        <button onclick="window.print()" class="btn-imprimir">🖨️ Imprimir Factura</button>
        
        <a href="<?= $url_volver ?>" class="btn-volver"><?= $texto_volver ?></a>
        
        <?php if ($rol_usuario === 'admin'): ?>
            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="btn-panel">Panel del Administrador</a>
        <?php else: ?>
            <a href="/Proyecto_aula/proyecto/views/menu/index.php" class="btn-panel">← Volver al Menú</a>
        <?php endif; ?>
    </div>

    <div class="factura-container">
        <!-- Header -->
        <div class="header">
            <div class="empresa">
                <h1>🍽️ Restaurante Bambino</h1>
                <p>Calle Principal #123</p>
                <p>Tel: (123) 456-7890</p>
                <p>Email: info@restaurante.com</p>
                <p>NIT: 900.123.456-7</p>
            </div>
            <div class="factura-info">
                <h2>FACTURA</h2>
                <p><strong><?= htmlspecialchars($factura['numero_factura']) ?></strong></p>
                <p>Fecha: <?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?></p>
                <p>Pedido: #<?= $factura['id_pedido'] ?></p>
                <p>Ref. Pago: <?= htmlspecialchars($factura['referencia_pago']) ?></p>
            </div>
        </div>

        <!-- Cliente -->
        <div class="cliente">
            <h3>CLIENTE</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($factura['nombre_usuario']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($factura['correo']) ?></p>
            <p><strong>Método de Pago:</strong> <?= ucfirst($factura['metodo_pago']) ?></p>
        </div>

        <!-- Productos -->
        <table>
            <thead>
                <tr>
                    <th>PRODUCTO</th>
                    <th>CANT.</th>
                    <th>PRECIO UNIT.</th>
                    <th>SUBTOTAL</th>
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

        <!-- Totales -->
        <div class="totales">
            <div class="total-linea">
                <span>Subtotal:</span>
                <strong>$<?= number_format($factura['subtotal'], 2) ?></strong>
            </div>
            <div class="total-linea">
                <span>IVA (19%):</span>
                <strong>$<?= number_format($factura['iva'], 2) ?></strong>
            </div>
            <div class="total-final">
                <span>TOTAL:</span>
                <span>$<?= number_format($factura['total'], 2) ?></span>
            </div>
        </div>

        <!-- Notas -->
        <?php if (!empty($factura['notas'])): ?>
            <div class="notas">
                <h4>NOTAS</h4>
                <p><?= nl2br(htmlspecialchars($factura['notas'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Gracias por su compra</p>
            <p>Esta es una factura generada electrónicamente</p>
            <p>Para cualquier consulta, contáctenos: info@restaurante.com</p>
        </div>
    </div>
</body>
</html>