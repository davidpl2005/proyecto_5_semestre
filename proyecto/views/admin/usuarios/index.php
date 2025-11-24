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
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/usuarios.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header con estilo consistente -->
        <header class="admin-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h1 style="margin: 0; color: white; font-size: 28px;">👥 Gestión de Usuarios</h1>
                <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="btn-logout" style="background-color: #34495e;">← Volver al Panel</a>
            </div>
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

        <table class="table pedidos-table">
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
                    <td><span class="pedido-id"><?= htmlspecialchars($usuario['id_usuario']) ?></span></td>
                    <td>
                        <div class="cliente-info">
                            <span class="cliente-nombre"><?= htmlspecialchars($usuario['nombre']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="cliente-email"><?= htmlspecialchars($usuario['correo']) ?></span>
                    </td>
                    <td>
                        <?php
                        // Definir estilos y texto según el rol
                        $rol_info = [
                            'admin' => [
                                'bg' => 'rgba(102, 126, 234, 0.15)',
                                'color' => '#0624adff',
                                'icono' => '👑',
                                'texto' => 'Administrador'
                            ],
                            'chef' => [
                                'bg' => 'rgba(230, 126, 34, 0.15)',
                                'color' => '#e67e22',
                                'icono' => '👨‍🍳',
                                'texto' => 'Chef'
                            ],
                            'cliente' => [
                                'bg' => 'rgba(53, 176, 176, 0.15)',
                                'color' => '#1289a0ff',
                                'icono' => '👤',
                                'texto' => 'Cliente'
                            ]
                        ];
                        $rol = $usuario['rol'] === 'admin' ? 'admin' : ($usuario['rol'] === 'chef' ? 'chef' : 'cliente');
                        $info = $rol_info[$rol];
                        ?>
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; background-color: <?= $info['bg'] ?>; color: <?= $info['color'] ?>;">
                            <?= $info['icono'] ?> <?= $info['texto'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $usuario['id_usuario'] ?>" class="btn-ver-admin">Editar</a>
                        <?php if ($usuario['rol'] !== 'admin' && $usuario['rol'] !== 'chef'): ?>
                            <a href="/Proyecto_aula/proyecto/controllers/AdminUsuarioController.php?action=delete&id=<?= $usuario['id_usuario'] ?>" 
                               class="btn-eliminar-admin" 
                               onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                Eliminar
                            </a>
                        <?php else: ?>
                            <span style="color: #7f8c8d; font-size: 12px; font-style: italic; padding: 6px 12px;">
                                🔒 Protegido
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #f39c12;">
            <h4 style="color: #856404; margin-bottom: 10px;">ℹ️ Información sobre usuarios protegidos</h4>
            <p style="color: #856404; margin: 0; font-size: 14px;">
                Los usuarios con rol <strong>Admin</strong> y <strong>Chef</strong> están protegidos y no pueden ser eliminados 
                para mantener la integridad del sistema. Solo pueden ser editados.
            </p>
        </div>
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