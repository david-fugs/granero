<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Granero</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <!-- Frutas decorativas flotantes -->
        <div class="fruit-decoration fruit-1">🍎</div>
        <div class="fruit-decoration fruit-2">🍊</div>
        <div class="fruit-decoration fruit-3">🍌</div>
        <div class="fruit-decoration fruit-4">🍇</div>
        
        <div class="login-container">
            <div class="login-loading" id="loginLoading">
                <div class="spinner spinner-large"></div>
            </div>
            
            <div class="login-header">
                <div class="login-logo">
                    <div class="login-logo-emoji">🍎</div>
                </div>
                <h1 class="login-title">Granero</h1>
                <p class="login-subtitle">Control de Inventario de Frutas</p>
            </div>
            
            <div class="login-body">
                <div class="welcome-message">
                    <h3>¡Bienvenido de nuevo! 👋</h3>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>
                
                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <div class="input-with-icon">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="tu@email.com"
                                required
                                autocomplete="email"
                            >
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-with-icon">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-lock"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Recordarme</label>
                        </div>
                        <a href="#" class="forgot-password" onclick="showForgotPassword(); return false;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <span id="btnText">Iniciar Sesión</span>
                        <div class="spinner d-none" id="btnSpinner"></div>
                    </button>
                </form>
            </div>
            
            <!-- Características del sistema -->
            <div class="login-features">
                <div class="feature-item">
                    <div class="feature-icon">📦</div>
                    <div class="feature-text">Control de Stock</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📋</div>
                    <div class="feature-text">Albaranes</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <div class="feature-text">Reportes</div>
                </div>
            </div>
            
            <div class="login-footer">
                <p>&copy; 2025 Granero. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- JavaScript -->
    <script src="../assets/js/app.js"></script>
    
    <script>
        // Cargar email recordado si existe
        document.addEventListener('DOMContentLoaded', function() {
            const rememberedEmail = getCookie('remember_email');
            if (rememberedEmail) {
                document.getElementById('email').value = rememberedEmail;
                document.getElementById('remember').checked = true;
            }
        });
        
        // Procesar formulario de login
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            if (!email || !password) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos vacíos',
                    text: 'Por favor complete todos los campos',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            // Mostrar loading
            const loginContainer = document.querySelector('.login-container');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                if (remember) {
                    formData.append('remember', '1');
                }
                
                const response = await fetch('../controllers/AuthController.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido! 🎉',
                        text: 'Redirigiendo al sistema...',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    btnText.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');
                    
                    // Animación de shake
                    loginContainer.classList.add('shake');
                    setTimeout(() => {
                        loginContainer.classList.remove('shake');
                    }, 500);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de autenticación',
                        text: result.message,
                        confirmButtonColor: '#667eea'
                    });
                }
            } catch (error) {
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error al iniciar sesión',
                    confirmButtonColor: '#667eea'
                });
                console.error('Error:', error);
            }
        });
        
        // Función para mostrar recuperación de contraseña
        function showForgotPassword() {
            Swal.fire({
                title: '🔐 Recuperar Contraseña',
                html: `
                    <p style="margin-bottom: 1.5rem; color: #6b7280;">
                        Por favor contacte al administrador del sistema para recuperar su contraseña.
                    </p>
                    <div style="text-align: left;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Email de contacto:</label>
                        <input type="email" id="forgotEmail" class="swal2-input" placeholder="su@email.com" style="width: 100%; margin: 0; font-size: 1rem;">
                    </div>
                `,
                icon: 'info',
                confirmButtonText: '📧 Enviar Solicitud',
                confirmButtonColor: '#667eea',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    const email = document.getElementById('forgotEmail').value;
                    if (!email) {
                        Swal.showValidationMessage('Por favor ingrese un email');
                        return false;
                    }
                    if (!/\S+@\S+\.\S+/.test(email)) {
                        Swal.showValidationMessage('Por favor ingrese un email válido');
                        return false;
                    }
                    return email;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Solicitud Enviada',
                        text: 'El administrador se pondrá en contacto con usted pronto',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
        }
        
        // Función para obtener cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return '';
        }
    </script>
</body>
</html>
