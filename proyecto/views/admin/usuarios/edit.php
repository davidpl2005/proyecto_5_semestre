<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Usuario.php';
checkAdmin();

$id = $_GET['id'] ?? 0;
$model = new Usuario();
$usuario = $model->findById($id);

if (!$usuario) {
    header('Location: /Proyecto_aula/proyecto/views/admin/usuarios/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/usuarios.css">
</head>
<body>
    <div class="admin-container">
        <h1>Editar Usuario</h1>
        
        <form action="/Proyecto_aula/proyecto/controllers/AdminUsuarioController.php?action=update" method="POST" class="form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
            
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol" <?= $usuario['rol'] === 'admin' ? 'disabled' : '' ?>>
                    <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <div class="form-actions">
                <a href="/Proyecto_aula/proyecto/views/admin/usuarios/index.php" class="btn">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>