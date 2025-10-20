<?php
$pageName = 'Dashboard';
$pageTitle = 'Dashboard';
include __DIR__ . '/layouts/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-details">
            <h3 id="totalClientes">0</h3>
            <p>Total Clientes</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-details">
            <h3 id="totalArticulos">0</h3>
            <p>Artículos en Stock</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-bookmark"></i>
        </div>
        <div class="stat-details">
            <h3 id="reservasPendientes">0</h3>
            <p>Reservas Pendientes</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-details">
            <h3 id="albaranesPendientes">0</h3>
            <p>Albaranes Pendientes</p>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <!-- Últimas Reservas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bookmark"></i>
                Últimas Reservas
            </h3>
            <a href="reservas.php" class="btn btn-sm btn-primary">
                Ver Todas
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nº Reserva</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="ultimasReservas">
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Cargando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Últimos Albaranes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice"></i>
                Últimos Albaranes
            </h3>
            <a href="albaranes.php" class="btn btn-sm btn-primary">
                Ver Todos
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nº Albarán</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="ultimosAlbaranes">
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Cargando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Stock Bajo -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exclamation-triangle text-warning"></i>
            Artículos con Stock Bajo
        </h3>
        <a href="stock.php" class="btn btn-sm btn-warning">
            Ver Stock Completo
        </a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Stock Disponible</th>
                        <th>Reservado</th>
                        <th>Stock Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="stockBajo">
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$customJS = '<script src="../assets/js/dashboard.js"></script>';
include __DIR__ . '/layouts/footer.php';
?>
