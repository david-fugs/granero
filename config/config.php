<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a base de datos
require_once __DIR__ . '/../conexion.php';

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (cambiar a 0 en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la aplicación
define('APP_NAME', 'Granero');
define('APP_VERSION', '1.0.0');

// Funciones auxiliares
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function hasPermission($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['tipo_usuario'] ?? '';
    
    // Admin tiene acceso a todo
    if ($userRole === 'admin') {
        return true;
    }
    
    // Verificar permisos específicos
    $permissions = [
        'admin' => ['usuarios', 'clientes', 'stock', 'movimientos', 'reservas', 'albaranes', 'comerciales', 'mozos', 'dashboard'],
        'vendedor' => ['clientes', 'reservas', 'albaranes', 'dashboard'],
        'almacen' => ['stock', 'movimientos', 'albaranes', 'dashboard'],
        'visualizador' => ['dashboard']
    ];
    
    if (isset($permissions[$userRole])) {
        return in_array($requiredRole, $permissions[$userRole]);
    }
    
    return false;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return number_format($amount, 2, ',', '.') . ' €';
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

function generateRandomCode($prefix = '', $length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $prefix . $code;
}
?>
