<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
checkAuth();

$id_pedido = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/pedidos.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmacion-card">
        <div class="success-icon">
            <span class="checkmark">✓</span>
        </div>

        <h1>¡Pedido Confirmado!</h1>
        <div class="pedido-numero-grande">Pedido #<?= htmlspecialchars($id_pedido) ?></div>

        <p>
            Tu pedido ha sido registrado exitosamente y está siendo procesado. 
            Recibirás una notificación cuando esté listo para entregar.
        </p>

        <div class="acciones">
            <a href="/Proyecto_aula/proyecto/controllers/PedidoController.php?action=view&id=<?= $id_pedido ?>" 
               class="btn-primary">
                📋 Ver Detalles del Pedido
            </a>
            <a href="/Proyecto_aula/proyecto/views/pedidos/index.php" 
               class="btn-secondary">
                📦 Mis Pedidos
            </a>
            <a href="/Proyecto_aula/proyecto/views/menu/index.php" 
               class="btn-secondary">
                🍽️ Seguir Comprando
            </a>
        </div>

        <div class="info-box">
            <h3>¿Qué sigue ahora?</h3>
            <ul>
                <li>Tu pedido será preparado por nuestro equipo</li>
                <li>Puedes ver el estado en tiempo real desde "Mis Pedidos"</li>
                <li>Te notificaremos cuando esté listo</li>
                <li>Si tienes alguna duda, contáctanos</li>
            </ul>
        </div>
    </div>
</body>
</html>