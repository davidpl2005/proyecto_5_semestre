<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Producto.php';
checkAdmin();

$model = new Producto();
$productos = $model->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/productos.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Productos</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div style="margin-bottom: 20px; display: flex; gap: 10px;">
            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" style="display: inline-block; padding: 10px 20px; background-color: #34495e; color: white; text-decoration: none; border-radius: 5px;">← VOLVER AL PANEL DE ADMINISTRACION</a>
            <a href="/Proyecto_aula/proyecto/views/admin/productos/create.php" style="display: inline-block; padding: 10px 20px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px;">+ AÑADIR NUEVO PRODUCTO</a>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                        No hay productos registrados. <a href="/Proyecto_aula/proyecto/views/admin/productos/create.php" style="color: #3498db;">Crear el primero</a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id_producto']) ?></td>
                        <td>
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                            <?php else: ?>
                                <span style="color: #95a5a6; font-size: 12px;">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td><strong style="color: #27ae60;">$<?= number_format($p['precio'], 2) ?></strong></td>
                        <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
                        <td>
                            <span class="status-badge <?= $p['disponible'] ? 'status-disponible' : 'status-no-disponible' ?>">
                                <?= $p['disponible'] ? 'Disponible' : 'No disponible' ?>
                            </span>
                        </td>
                        <td>
                            <a href="/Proyecto_aula/proyecto/views/admin/productos/edit.php?id=<?= $p['id_producto'] ?>" class="btn-action btn-edit">Editar</a>
                            <a href="/Proyecto_aula/proyecto/controllers/ProductoController.php?action=delete&id=<?= $p['id_producto'] ?>" 
                               class="btn-action btn-delete" 
                               onclick="return confirm('¿Está seguro de eliminar este producto?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>