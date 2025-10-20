<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Articulo.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$movimientoModel = new Movimiento();
$articuloModel = new Articulo();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'busqueda' => $_GET['busqueda'] ?? '',
            'articulo_id' => $_GET['articulo_id'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $resultado = $movimientoModel->listar($filtros);
        $total = $movimientoModel->contar($filtros);
        
        echo json_encode([
            'success' => true,
            'movimientos' => $resultado['movimientos'] ?? [],
            'total' => $total,
            'pagina_actual' => $pagina
        ]);
        break;
        
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $movimiento = $movimientoModel->obtenerPorId($id);
        
        if ($movimiento) {
            echo json_encode([
                'success' => true,
                'data' => $movimiento
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Movimiento no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('movimientos')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para registrar movimientos'
            ]);
            exit;
        }
        
        $datos = [
            'articulo_id' => intval($_POST['articulo_id'] ?? 0),
            'tipo_movimiento' => $_POST['tipo_movimiento'] ?? '',
            'cantidad' => floatval($_POST['cantidad'] ?? 0),
            'stock_disponible' => floatval($_POST['stock_disponible'] ?? 0),
            'cantidad_albaranes' => floatval($_POST['cantidad_albaranes'] ?? 0),
            'reservado' => floatval($_POST['reservado'] ?? 0),
            'nombre_comercial' => sanitize($_POST['nombre_comercial'] ?? ''),
            'stock_sage' => floatval($_POST['stock_sage'] ?? 0),
            'observaciones' => sanitize($_POST['observaciones'] ?? ''),
            'usuario_id' => $_SESSION['user_id']
        ];
        
        if ($datos['articulo_id'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe seleccionar un artículo'
            ]);
            exit;
        }
        
        if (empty($datos['tipo_movimiento'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe seleccionar un tipo de movimiento'
            ]);
            exit;
        }
        
        if ($datos['cantidad'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'La cantidad debe ser mayor a 0'
            ]);
            exit;
        }
        
        $resultado = $movimientoModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('movimientos')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar movimientos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $movimientoModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'listar_articulos':
        $resultado = $articuloModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'articulos' => $resultado['articulos'] ?? []
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}
