<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <form action="/Proyecto_aula/proyecto/controllers/AuthController.php?action=login" method="POST" class="form-login">
                <h2>Iniciar Sesión</h2>
                <p class="form-subtitle">Accede a tu cuenta</p>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <span class="icon">⚠️</span>
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <span class="icon">✓</span>
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" 
                           id="correo" 
                           name="correo" 
                           placeholder="ejemplo@correo.com"
                           required 
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Ingresa tu contraseña"
                               required 
                               autocomplete="current-password">
                        <button type="button" 
                                class="toggle-password" 
                                onclick="togglePassword('password')"
                                aria-label="Mostrar contraseña">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Iniciar Sesión</button>
                
                <p class="register-link">
                    ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
                button.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                input.type = 'password';
                button.textContent = '👁️';
                button.setAttribute('aria-label', 'Mostrar contraseña');
            }
        }

        // Prevenir envío múltiple del formulario
        document.querySelector('.form-login').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Iniciando sesión...';
        });
    </script>
</body>
</html>