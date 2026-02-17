<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
checkAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/productos.css">
</head>
<body>
    <div class="container">
        <h2>Crear Nuevo Producto</h2>
        
        <form action="/Proyecto_aula/proyecto/controllers/ProductoController.php?action=create" method="post" enctype="multipart/form-data">
            <label>
                Nombre del Producto
                <input type="text" name="nombre" required placeholder="Ej: Laptop HP">
            </label>

            <label>
                Descripción
                <textarea name="descripcion" placeholder="Describe las características del producto..."></textarea>
            </label>

            <label>
                Precio
                <input type="number" step="0.01" name="precio" required placeholder="0.00">
            </label>

            <label>
                Categoría
                <input type="text" name="categoria" placeholder="Ej: Electrónica">
            </label>

            <label>
                Imagen del Producto
                <input type="file" name="imagen" accept="image/*">
            </label>

            <button type="submit">Crear Producto</button>
        </form>
        
        <p><a href="/Proyecto_aula/proyecto/views/admin/productos/index.php">← Volver a Productos</a></p>
    </div>
</body>
</html>