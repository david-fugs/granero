<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Articulo.php';
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/../models/Albaran.php';

class DashboardController {
    private $clienteModel;
    private $articuloModel;
    private $reservaModel;
    private $albaranModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
        $this->articuloModel = new Articulo();
        $this->reservaModel = new Reserva();
        $this->albaranModel = new Albaran();
    }
    
    public function estadisticas() {
        try {
            $stats = [
                'totalClientes' => $this->clienteModel->contar(['activo' => 1]),
                'totalArticulos' => $this->articuloModel->contar(['activo' => 1]),
                'reservasPendientes' => $this->reservaModel->contar(['estado' => 'reservado']),
                'albaranesPendientes' => $this->albaranModel->contarPorEstado(['pendiente', 'en_preparacion', 'pendiente_facturar'])
            ];
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }
    
    public function ultimasReservas() {
        try {
            $reservas = $this->reservaModel->listar([
                'limit' => 5,
                'offset' => 0,
                'order' => 'fecha DESC'
            ]);
            
            echo json_encode([
                'success' => true,
                'reservas' => $reservas
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener reservas'
            ]);
        }
    }
    
    public function ultimosAlbaranes() {
        try {
            $albaranes = $this->albaranModel->listar([
                'limit' => 5,
                'offset' => 0,
                'order' => 'fecha DESC'
            ]);
            
            echo json_encode([
                'success' => true,
                'albaranes' => $albaranes
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener albaranes'
            ]);
        }
    }
    
    public function stockBajo() {
        try {
            $articulos = $this->articuloModel->obtenerStockBajo(50, 10);
            
            echo json_encode([
                'success' => true,
                'articulos' => $articulos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener stock bajo'
            ]);
        }
    }
}

// Procesar peticiones
if (isset($_GET['action'])) {
    $controller = new DashboardController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    }
}
?>
