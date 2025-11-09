<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Restaurante</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <form action="/Proyecto_aula/proyecto/controllers/AuthController.php?action=register" method="POST" class="form-register" id="registerForm">
                <h2>Crear Cuenta</h2>
                <p class="form-subtitle">Regístrate para comenzar</p>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <span class="icon">⚠️</span>
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input id="nombre" 
                           type="text" 
                           name="nombre" 
                           required
                           autocomplete="name"
                           minlength="3">
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" 
                           type="email" 
                           name="correo" 
                           placeholder="ejemplo@correo.com"
                           required
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required
                               autocomplete="new-password"
                               minlength="3">
                        <button type="button" 
                                class="toggle-password" 
                                onclick="togglePassword('password')"
                                aria-label="Mostrar contraseña">
                            👁️
            
                        </button>
                   <br><br>
                <div class="form-group">
                    <label for="password2">Confirmar contraseña</label>
                    <div class="password-wrapper">
                        <input id="password2" 
                               type="password" 
                               name="password2" 
                               placeholder="Repite tu contraseña"
                               required
                               autocomplete="new-password"
                               minlength="3">
                        <button type="button" 
                                class="toggle-password" 
                                onclick="togglePassword('password2')"
                                aria-label="Mostrar contraseña">
                            👁️
                        </button>
                    </div>
                    <span id="passwordError" class="error-message" style="display: none;">Las contraseñas no coinciden</span>
                </div>

                <button type="submit" class="btn-submit">Registrarse</button>

                <p class="login-link">
                    ¿Ya tienes cuenta? <a href="/Proyecto_aula/proyecto/views/auth/login.php">Iniciar sesión</a>
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

        // Validación en tiempo real de contraseñas
        const password = document.getElementById('password');
        const password2 = document.getElementById('password2');
        const passwordError = document.getElementById('passwordError');
        const form = document.getElementById('registerForm');

        function validatePasswords() {
            if (password2.value === '') {
                passwordError.style.display = 'none';
                password2.classList.remove('error', 'success');
                return;
            }

            if (password.value !== password2.value) {
                passwordError.style.display = 'block';
                password2.classList.add('error');
                password2.classList.remove('success');
            } else {
                passwordError.style.display = 'none';
                password2.classList.remove('error');
                password2.classList.add('success');
            }
        }

        password.addEventListener('input', validatePasswords);
        password2.addEventListener('input', validatePasswords);

        // Validación antes de enviar
        form.addEventListener('submit', function(e) {
            if (password.value !== password2.value) {
                e.preventDefault();
                passwordError.style.display = 'block';
                password2.focus();
                return false;
            }

            // Deshabilitar botón y mostrar estado de carga
            const submitBtn = this.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registrando...';
        });

        // Validación de longitud mínima
        password.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 3) {
                this.setCustomValidity('La contraseña debe tener al menos 3 caracteres');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>