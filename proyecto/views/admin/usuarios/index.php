<?php
session_start();
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../models/Usuario.php';
checkAdmin();

$model = new Usuario();
$usuarios = $model->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Admin</title>
     <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/usuarios.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestión de Usuarios</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="actions">
            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="btn">← Volver</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $usuario['id_usuario'] ?>" class="btn-edit">Editar</a>
                        <?php if ($usuario['rol'] !== 'admin'): ?>
                            <a href="/Proyecto_aula/proyecto/controllers/AdminUsuarioController.php?action=delete&id=<?= $usuario['id_usuario'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                Eliminar
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>