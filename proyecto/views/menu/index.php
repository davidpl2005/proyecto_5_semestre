<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Producto.php';
checkAuth();

$model = new Producto();
$productos = $model->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/menu.css">
</head>
<body>
    <div class="menu-container">
        <header class="menu-header">
            <h1>Nuestro Menú</h1>
            <div class="user-info">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre']) ?></span>
                <a href="/Proyecto_aula/proyecto/controllers/AuthController.php?action=logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <div class="productos-grid">
            <?php foreach ($productos as $p): ?>
                <?php if (!$p['disponible']) continue; ?>
                <div class="producto-card">
                    <?php if (!empty($p['imagen'])): ?>
                        <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($p['imagen']) ?>" 
                             alt="<?= htmlspecialchars($p['nombre']) ?>" 
                             class="producto-imagen">
                    <?php endif; ?>
                    <div class="producto-info">
                        <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                        <p class="producto-descripcion"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                        <p class="producto-precio">$<?= number_format($p['precio'], 2) ?></p>
                        <form method="post" action="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=add" 
                              class="form-agregar">
                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                            <div class="cantidad-wrapper">
                                <label for="cantidad-<?= $p['id_producto'] ?>">Cantidad:</label>
                                <input type="number" 
                                       id="cantidad-<?= $p['id_producto'] ?>" 
                                       name="cantidad" 
                                       value="1" 
                                       min="1" 
                                       class="input-cantidad">
                            </div>
                            <button type="submit" class="btn-agregar">Agregar al Carrito</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="carrito-preview">
            <a href="/Proyecto_aula/proyecto/views/carrito/index.php" class="btn-carrito">
                Ver Carrito
                <?php if (!empty($_SESSION['carrito'])): ?>
                    <span class="carrito-cantidad"><?= count($_SESSION['carrito']) ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</body>
</html>