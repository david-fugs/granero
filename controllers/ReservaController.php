<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Comercial.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$reservaModel = new Reserva();
$clienteModel = new Cliente();
$comercialModel = new Comercial();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'busqueda' => $_GET['busqueda'] ?? '',
            'cliente_id' => $_GET['cliente_id'] ?? '',
            'comercial_id' => $_GET['comercial_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $resultado = $reservaModel->listar($filtros);
        $total = $reservaModel->contar($filtros);
        
        echo json_encode([
            'success' => true,
            'reservas' => $resultado['reservas'] ?? [],
            'total' => $total,
            'pagina_actual' => $pagina
        ]);
        break;
        
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $reserva = $reservaModel->obtenerPorId($id);
        
        if ($reserva) {
            echo json_encode([
                'success' => true,
                'reserva' => $reserva
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Reserva no encontrada'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('reservas')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear reservas'
            ]);
            exit;
        }
        
        $datos = [
            'numero_reserva' => sanitize($_POST['numero_reserva'] ?? ''),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'cliente_id' => intval($_POST['cliente_id'] ?? 0),
            'comercial_id' => intval($_POST['comercial_id'] ?? 0),
            'transportista' => sanitize($_POST['transportista'] ?? ''),
            'plataforma_carga' => sanitize($_POST['plataforma_carga'] ?? ''),
            'estado' => $_POST['estado'] ?? 'reservado',
            'faltan_precios' => isset($_POST['faltan_precios']) ? 1 : 0,
            'prepago' => isset($_POST['prepago']) ? 1 : 0,
            'observaciones' => sanitize($_POST['observaciones'] ?? '')
        ];
        
        if (empty($datos['numero_reserva'])) {
            echo json_encode([
                'success' => false,
                'message' => 'El número de reserva es obligatorio'
            ]);
            exit;
        }
        
        if ($datos['cliente_id'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe seleccionar un cliente'
            ]);
            exit;
        }
        
        $resultado = $reservaModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('reservas')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar reservas'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'numero_reserva' => sanitize($_POST['numero_reserva'] ?? ''),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'cliente_id' => intval($_POST['cliente_id'] ?? 0),
            'comercial_id' => intval($_POST['comercial_id'] ?? 0),
            'transportista' => sanitize($_POST['transportista'] ?? ''),
            'plataforma_carga' => sanitize($_POST['plataforma_carga'] ?? ''),
            'estado' => $_POST['estado'] ?? 'reservado',
            'faltan_precios' => isset($_POST['faltan_precios']) ? 1 : 0,
            'prepago' => isset($_POST['prepago']) ? 1 : 0,
            'observaciones' => sanitize($_POST['observaciones'] ?? '')
        ];
        
        $resultado = $reservaModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('reservas')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar reservas'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $reservaModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'listar_clientes':
        $resultado = $clienteModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'clientes' => $resultado['clientes'] ?? []
        ]);
        break;
        
    case 'listar_comerciales':
        $resultado = $comercialModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'comerciales' => $resultado['comerciales'] ?? []
        ]);
        break;
        
    case 'generar_numero':
        $numero = $reservaModel->generarNumeroReserva();
        echo json_encode([
            'success' => true,
            'numero' => $numero
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}
