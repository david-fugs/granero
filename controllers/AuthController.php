<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    // Login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (empty($email) || empty($password)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Por favor complete todos los campos'
                ]);
                return;
            }
            
            $resultado = $this->usuarioModel->verificarCredenciales($email, $password);
            
            if ($resultado['success']) {
                // Iniciar sesión
                $_SESSION['user_id'] = $resultado['usuario']['id'];
                $_SESSION['user_name'] = $resultado['usuario']['nombre'];
                $_SESSION['user_email'] = $resultado['usuario']['email'];
                $_SESSION['tipo_usuario'] = $resultado['usuario']['tipo_usuario'];
                
                // Recordar usuario si se marcó la opción
                if ($remember) {
                    setcookie('remember_email', $email, time() + (86400 * 30), '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Inicio de sesión exitoso',
                    'redirect' => BASE_URL . 'views/dashboard.php'
                ]);
            } else {
                echo json_encode($resultado);
            }
        }
    }
    
    // Logout
    public function logout() {
        session_destroy();
        setcookie('remember_email', '', time() - 3600, '/');
        redirect('views/login.php');
    }
    
    // Verificar sesión
    public function verificarSesion() {
        if (isLoggedIn()) {
            echo json_encode([
                'success' => true,
                'usuario' => [
                    'id' => $_SESSION['user_id'],
                    'nombre' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'tipo_usuario' => $_SESSION['tipo_usuario']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No hay sesión activa'
            ]);
        }
    }
}

// Procesar peticiones
if (isset($_GET['action'])) {
    $controller = new AuthController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    }
}
?>
