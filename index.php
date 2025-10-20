<?php
require_once 'config/config.php';

// Si el usuario está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('views/dashboard.php');
} else {
    redirect('views/login.php');
}
?>
