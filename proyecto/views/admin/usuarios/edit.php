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

// Determinar si el rol está bloqueado (admin o chef)
$rol_bloqueado = ($usuario['rol'] === 'admin' || $usuario['rol'] === 'chef');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - Admin</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/admin-pedidos.css">
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/usuarios.css">
    <style>
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-container h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group select:disabled,
        .form-group input:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .rol-bloqueado-info {
            background-color: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 12px 15px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 13px;
            color: #856404;
        }
        
        .rol-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .rol-admin {
            background-color: rgba(102, 126, 234, 0.15);
            color: #667eea;
        }
        
        .rol-chef {
            background-color: rgba(230, 126, 34, 0.15);
            color: #e67e22;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h1 style="margin: 0; color: white; font-size: 28px;">✏️ Editar Usuario</h1>
                <a href="/Proyecto_aula/proyecto/views/admin/usuarios/index.php" class="btn-logout" style="background-color: #34495e;">← Volver</a>
            </div>
        </header>
        
        <div class="form-container">
            <h1>Editar Usuario</h1>
            
            <form action="/Proyecto_aula/proyecto/controllers/AdminUsuarioController.php?action=update" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" 
                           id="nombre"
                           name="nombre" 
                           value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                           required
                           placeholder="Nombre completo del usuario">
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" 
                           id="correo"
                           name="correo" 
                           value="<?= htmlspecialchars($usuario['correo']) ?>" 
                           required
                           placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" 
                           id="password"
                           name="password"
                           placeholder="Dejar en blanco para no cambiar">
                    <small style="display: block; margin-top: 5px; color: #7f8c8d;">
                        Solo ingresa una contraseña si deseas cambiarla
                    </small>
                </div>

                <div class="form-group">
                    <label for="rol">Rol del Usuario</label>
                    
                    <?php if ($rol_bloqueado): ?>
                        <!-- Campo deshabilitado con valor visible -->
                        <select name="rol" id="rol" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                            <option value="<?= $usuario['rol'] ?>" selected>
                                <?php if ($usuario['rol'] === 'admin'): ?>
                                    👑 Administrador
                                <?php elseif ($usuario['rol'] === 'chef'): ?>
                                    👨‍🍳 Chef
                                <?php endif; ?>
                            </option>
                        </select>
                        
                        <!-- Campo oculto para enviar el valor real -->
                        <input type="hidden" name="rol" value="<?= $usuario['rol'] ?>">
                        
                        <!-- Badge visual -->
                        <div>
                            <span class="rol-badge <?= $usuario['rol'] === 'admin' ? 'rol-admin' : 'rol-chef' ?>">
                                <?php if ($usuario['rol'] === 'admin'): ?>
                                    👑 Administrador
                                <?php else: ?>
                                    👨‍🍳 Chef
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <!-- Mensaje informativo -->
                        <div class="rol-bloqueado-info">
                            🔒 El rol de <strong><?= $usuario['rol'] === 'admin' ? 'Administrador' : 'Chef' ?></strong> 
                            está protegido y no puede ser modificado para mantener la integridad del sistema.
                        </div>
                    <?php else: ?>
                        <!-- Campo bloqueado también para clientes -->
                        <select name="rol" id="rol" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                            <option value="cliente" selected>
                                👤 Cliente
                            </option>
                        </select>
                        
                        <!-- Campo oculto para enviar el valor real -->
                        <input type="hidden" name="rol" value="cliente">
                        
                        <!-- Badge visual -->
                        <div>
                            <span class="rol-badge" style="background-color: rgba(52, 152, 219, 0.15); color: #3498db;">
                                👤 Cliente
                            </span>
                        </div>
                        
                        <!-- Mensaje informativo -->
                        <div class="rol-bloqueado-info">
                            🔒 El rol de <strong>Cliente</strong> está protegido. 
                            Los clientes no pueden ser promovidos a Chef o Administrador desde esta interfaz.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <a href="/Proyecto_aula/proyecto/views/admin/usuarios/index.php" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Prevenir envío múltiple
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Guardando...';
        });
    </script>
</body>
</html>