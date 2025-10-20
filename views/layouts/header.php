<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

$pageName = $pageName ?? 'Dashboard';
$pageTitle = $pageTitle ?? 'Granero';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Granero</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <?php if (isset($customCSS)) echo $customCSS; ?>
</head>
<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-apple-alt"></i>
                    <h2>Granero</h2>
                </div>
                <div class="sidebar-controls">
                    <button class="sidebar-collapse" id="sidebarCollapse" title="Colapsar menú">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="sidebar-close" id="sidebarClose" title="Cerrar menú">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (hasPermission('usuarios')): ?>
                <li>
                    <a href="usuarios.php">
                        <i class="fas fa-users"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('clientes')): ?>
                <li>
                    <a href="clientes.php">
                        <i class="fas fa-user-tie"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('stock')): ?>
                <li>
                    <a href="stock.php">
                        <i class="fas fa-boxes"></i>
                        <span>Stock por Artículo</span>
                    </a>
                </li>
                <li>
                    <a href="stock_partidas.php">
                        <i class="fas fa-box-open"></i>
                        <span>Stock por Partida</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('movimientos')): ?>
                <li>
                    <a href="movimientos.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Movimientos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('reservas')): ?>
                <li>
                    <a href="reservas.php">
                        <i class="fas fa-bookmark"></i>
                        <span>Reservas</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('albaranes')): ?>
                <li>
                    <a href="albaranes.php">
                        <i class="fas fa-file-invoice"></i>
                        <span>Albaranes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('comerciales')): ?>
                <li>
                    <a href="comerciales.php">
                        <i class="fas fa-handshake"></i>
                        <span>Comerciales</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('mozos')): ?>
                <li>
                    <a href="mozos.php">
                        <i class="fas fa-people-carry"></i>
                        <span>Mozos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <a href="../controllers/AuthController.php?action=logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo $pageName; ?></h1>
                </div>
                
                <div class="topbar-right">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo $_SESSION['user_name']; ?></div>
                            <div class="user-role"><?php echo ucfirst($_SESSION['tipo_usuario']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
