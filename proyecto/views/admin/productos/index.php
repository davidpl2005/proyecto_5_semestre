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
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/productos.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header con estilo consistente -->
        <header class="admin-header">
            <h1>🍔 Gestión de Productos</h1>
            <br>
            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="btn-logout" style="background-color: #34495e;">← Volver al Panel</a>
        </header>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #d4edda; color: #155724;">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <a href="/Proyecto_aula/proyecto/views/admin/productos/create.php" 
               class="btn-entrada" 
               style="display: inline-block; padding: 10px 20px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;">
                + AÑADIR NUEVO PRODUCTO
            </a>
        </div>

        <table class="products-table pedidos-table">
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
                    <td colspan="7" class="table-empty">
                        No hay productos registrados. <a href="/Proyecto_aula/proyecto/views/admin/productos/create.php" style="color: #3498db;">Crear el primero</a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><span class="pedido-id"><?= htmlspecialchars($p['id_producto']) ?></span></td>
                        <td>
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($p['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($p['nombre']) ?>"
                                     style="height: 60px; border-radius: 5px;">
                            <?php else: ?>
                                <span style="color: #95a5a6; font-size: 12px;">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td><span class="precio-tabla">$<?= number_format($p['precio'], 2) ?></span></td>
                        <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
                        <td>
                            <span class="status-badge <?= $p['disponible'] ? 'status-disponible' : 'status-no-disponible' ?>" 
                                  style="padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;">
                                <?= $p['disponible'] ? '✓ Disponible' : '✗ No disponible' ?>
                            </span>
                        </td>
                        <td>
                            <a href="/Proyecto_aula/proyecto/views/admin/productos/edit.php?id=<?= $p['id_producto'] ?>" 
                               class="btn-ver-admin">
                                Editar
                            </a>
                            <a href="/Proyecto_aula/proyecto/controllers/ProductoController.php?action=delete&id=<?= $p['id_producto'] ?>" 
                               class="btn-eliminar-admin" 
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

    <script>
        // Auto-ocultar alertas después de 3 segundos
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