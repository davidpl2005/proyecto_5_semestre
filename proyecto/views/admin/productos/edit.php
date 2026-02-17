<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Producto.php';
checkAdmin();

$id = intval($_GET['id'] ?? 0);
$model = new Producto();
$item = $model->findById($id);
if (!$item) {
    header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/productos.css">
</head>
<body>
    <div class="container">
        <h2>Editar Producto #<?= $item['id_producto'] ?></h2>
        
        <form action="/Proyecto_aula/proyecto/controllers/ProductoController.php?action=edit" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $item['id_producto'] ?>">
            
            <label>
                Nombre del Producto
                <input type="text" name="nombre" value="<?= htmlspecialchars($item['nombre']) ?>" required>
            </label>

            <label>
                Descripción
                <textarea name="descripcion"><?= htmlspecialchars($item['descripcion']) ?></textarea>
            </label>

            <label>
                Precio
                <input type="number" step="0.01" name="precio" value="<?= $item['precio'] ?>" required>
            </label>

            <label>
                Categoría
                <input type="text" name="categoria" value="<?= htmlspecialchars($item['categoria']) ?>">
            </label>

            <label>
                Disponible
                <input type="checkbox" name="disponible" <?= $item['disponible'] ? 'checked' : '' ?>>
            </label>

            <?php if (!empty($item['imagen'])): ?>
                <p>
                    <strong>Imagen actual:</strong><br>
                    <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($item['imagen']) ?>" style="height:100px">
                </p>
            <?php endif; ?>

            <label>
                Cambiar imagen
                <input type="file" name="imagen" accept="image/*">
            </label>

            <button type="submit">Guardar Cambios</button>
        </form>
        
        <p><a href="/Proyecto_aula/proyecto/views/admin/productos/index.php">← Volver a Productos</a></p>
    </div>
</body>
</html>