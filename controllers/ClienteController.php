<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {
    private $clienteModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
    }
    
    // Listar clientes con paginación
    public function listar() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 25;
            $offset = ($page - 1) * $limit;
            
            $filtros = [
                'search' => $_GET['search'] ?? '',
                'activo' => isset($_GET['activo']) ? $_GET['activo'] : null,
                'paga_transporte' => isset($_GET['paga_transporte']) ? $_GET['paga_transporte'] : null,
                'limit' => $limit,
                'offset' => $offset
            ];
            
            $clientes = $this->clienteModel->listar($filtros);
            $total = $this->clienteModel->contar($filtros);
            
            echo json_encode([
                'success' => true,
                'clientes' => $clientes,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar clientes: ' . $e->getMessage()
            ]);
        }
    }
    
    // Obtener cliente por ID
    public function obtener() {
        try {
            $id = $_GET['id'] ?? 0;
            $cliente = $this->clienteModel->obtenerPorId($id);
            
            if ($cliente) {
                echo json_encode([
                    'success' => true,
                    'cliente' => $cliente
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener cliente: ' . $e->getMessage()
            ]);
        }
    }
    
    // Crear cliente
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'codigo_cliente' => $_POST['codigo_cliente'] ?? '',
                    'nombre_cliente' => $_POST['nombre_cliente'] ?? '',
                    'nif' => $_POST['nif'] ?? '',
                    'paga_transporte' => $_POST['paga_transporte'] ?? 'no',
                    'importe_riesgo' => $_POST['importe_riesgo'] ?? 0.00,
                    'deuda' => $_POST['deuda'] ?? 0.00,
                    'plat' => $_POST['plat'] ?? '',
                    'carga' => $_POST['carga'] ?? '',
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                // Validaciones
                if (empty($datos['codigo_cliente']) || empty($datos['nombre_cliente'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'El código y nombre del cliente son obligatorios'
                    ]);
                    return;
                }
                
                $resultado = $this->clienteModel->crear($datos);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear cliente: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    // Actualizar cliente
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['id'] ?? 0;
                
                $datos = [
                    'codigo_cliente' => $_POST['codigo_cliente'] ?? '',
                    'nombre_cliente' => $_POST['nombre_cliente'] ?? '',
                    'nif' => $_POST['nif'] ?? '',
                    'paga_transporte' => $_POST['paga_transporte'] ?? 'no',
                    'importe_riesgo' => $_POST['importe_riesgo'] ?? 0.00,
                    'deuda' => $_POST['deuda'] ?? 0.00,
                    'plat' => $_POST['plat'] ?? '',
                    'carga' => $_POST['carga'] ?? '',
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                // Validaciones
                if (empty($datos['codigo_cliente']) || empty($datos['nombre_cliente'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'El código y nombre del cliente son obligatorios'
                    ]);
                    return;
                }
                
                $resultado = $this->clienteModel->actualizar($id, $datos);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar cliente: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    // Eliminar cliente
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['id'] ?? 0;
                
                $resultado = $this->clienteModel->eliminar($id);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar cliente: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    // Buscar clientes para autocomplete
    public function buscar() {
        try {
            $termino = $_GET['q'] ?? '';
            
            if (strlen($termino) < 2) {
                echo json_encode([
                    'success' => true,
                    'clientes' => []
                ]);
                return;
            }
            
            $clientes = $this->clienteModel->buscar($termino);
            
            echo json_encode([
                'success' => true,
                'clientes' => $clientes
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar clientes: ' . $e->getMessage()
            ]);
        }
    }
}

// Procesar peticiones
if (isset($_GET['action'])) {
    $controller = new ClienteController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
    }
}
?>
