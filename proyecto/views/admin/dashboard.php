<?php

session_start();
require_once __DIR__ . '/../../middleware/auth.php';
checkAdmin();
require_once __DIR__ . '/../../models/Producto.php';

// Obtener estadísticas básicas
$productoModel = new Producto();
$productos = $productoModel->getAll();
$productosActivos = array_filter($productos, function($p) {
    return $p['disponible'] == 1;
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre']); ?></p>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="/Proyecto_aula/proyecto/views/admin/productos/index.php">Gestionar Productos</a></li>
                <li><a href="/Proyecto_aula/proyecto/views/admin/usuarios/index.php">Gestionar Usuarios</a></li>
                <li><a href="/Proyecto_aula/proyecto/views/admin/pedidos/index.php">Ver Pedidos</a></li>
                <li><a href="/Proyecto_aula/proyecto/controllers/AuthController.php?action=logout" class="btn-logout">Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <h2>Resumen</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Productos</h3>
                    <p><?= count($productos) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Productos Activos</h3>
                    <p><?= count($productosActivos) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Productos Inactivos</h3>
                    <p><?= count($productos) - count($productosActivos) ?></p>
                </div>
            </div>

            <div class="recent-actions">
                <h2>Acciones Rápidas</h2>
                <p>
                    <a href="/Proyecto_aula/proyecto/views/admin/productos/create.php" style="color: #3498db;">+ Agregar Nuevo Producto</a>
                </p>
            </div>
        </main>
    </div>
</body>
</html>