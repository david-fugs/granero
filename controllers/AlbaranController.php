<?php
// Evitar cualquier output antes del JSON
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Albaran.php';
require_once __DIR__ . '/../models/AlbaranArticulo.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Comercial.php';
require_once __DIR__ . '/../models/Mozo.php';
require_once __DIR__ . '/../models/Articulo.php';

requireLogin();

// Limpiar cualquier output buffering y establecer header
ob_clean();
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$albaranModel = new Albaran();
$albaranArticuloModel = new AlbaranArticulo();
$clienteModel = new Cliente();
$comercialModel = new Comercial();
$mozoModel = new Mozo();
$articuloModel = new Articulo();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'search' => $_GET['busqueda'] ?? '',
            'cliente_id' => $_GET['cliente_id'] ?? '',
            'comercial_id' => $_GET['comercial_id'] ?? '',
            'mozo_id' => $_GET['mozo_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $albaranes = $albaranModel->listar($filtros);
        $total = count($albaranes); // Por ahora, retornamos el count del array
        
        echo json_encode([
            'success' => true,
            'albaranes' => $albaranes,
            'total' => $total,
            'pagina_actual' => $pagina
        ]);
        break;
        
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $albaran = $albaranModel->obtenerPorId($id);
        
        if ($albaran) {
            // Obtener líneas de artículos
            $lineas = $albaranArticuloModel->listarPorAlbaran($id);
            $albaran['lineas'] = $lineas;
            
            echo json_encode([
                'success' => true,
                'albaran' => $albaran
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Albarán no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear albaranes'
            ]);
            exit;
        }
        
        $datos = [
            'numero_albaran' => sanitize($_POST['numero_albaran'] ?? ''),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'cliente_id' => intval($_POST['cliente_id'] ?? 0),
            'comercial_id' => intval($_POST['comercial_id'] ?? 0),
            'mozo_id' => intval($_POST['mozo_id'] ?? 0),
            'estado' => $_POST['estado'] ?? 'pendiente',
            'faltan_precios' => isset($_POST['faltan_precios']) ? 1 : 0,
            'preparado' => isset($_POST['preparado']) ? 1 : 0,
            'unificado' => isset($_POST['unificado']) ? 1 : 0,
            'prepago' => isset($_POST['prepago']) ? 1 : 0,
            'total_general' => floatval($_POST['total_general'] ?? 0),
            'total_peso' => floatval($_POST['total_peso'] ?? 0),
            'observaciones' => sanitize($_POST['observaciones'] ?? '')
        ];
        
        if (empty($datos['numero_albaran'])) {
            echo json_encode([
                'success' => false,
                'message' => 'El número de albarán es obligatorio'
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
        
        $resultado = $albaranModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar albaranes'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'numero_albaran' => sanitize($_POST['numero_albaran'] ?? ''),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'cliente_id' => intval($_POST['cliente_id'] ?? 0),
            'comercial_id' => intval($_POST['comercial_id'] ?? 0),
            'mozo_id' => intval($_POST['mozo_id'] ?? 0),
            'estado' => $_POST['estado'] ?? 'pendiente',
            'faltan_precios' => isset($_POST['faltan_precios']) ? 1 : 0,
            'preparado' => isset($_POST['preparado']) ? 1 : 0,
            'unificado' => isset($_POST['unificado']) ? 1 : 0,
            'prepago' => isset($_POST['prepago']) ? 1 : 0,
            'total_general' => floatval($_POST['total_general'] ?? 0),
            'total_peso' => floatval($_POST['total_peso'] ?? 0),
            'observaciones' => sanitize($_POST['observaciones'] ?? '')
        ];
        
        $resultado = $albaranModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar albaranes'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $albaranModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    // Gestión de líneas de artículos
    case 'agregar_articulo':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos'
            ]);
            exit;
        }
        
        $datos = [
            'albaran_id' => intval($_POST['albaran_id'] ?? 0),
            'articulo_id' => intval($_POST['articulo_id'] ?? 0),
            'partida' => sanitize($_POST['partida'] ?? ''),
            'unidades' => floatval($_POST['unidades'] ?? 0),
            'peso' => floatval($_POST['peso'] ?? 0),
            'precio' => floatval($_POST['precio'] ?? 0),
            'importe_transporte' => floatval($_POST['importe_transporte'] ?? 0)
        ];
        
        // Calcular importe
        $datos['importe'] = ($datos['peso'] * $datos['precio']) + $datos['importe_transporte'];
        
        $resultado = $albaranArticuloModel->crear($datos);
        
        if ($resultado['success']) {
            // Actualizar totales del albarán
            $albaranModel->recalcularTotales($datos['albaran_id']);
        }
        
        echo json_encode($resultado);
        break;
        
    case 'actualizar_articulo':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'articulo_id' => intval($_POST['articulo_id'] ?? 0),
            'partida' => sanitize($_POST['partida'] ?? ''),
            'unidades' => floatval($_POST['unidades'] ?? 0),
            'peso' => floatval($_POST['peso'] ?? 0),
            'precio' => floatval($_POST['precio'] ?? 0),
            'importe_transporte' => floatval($_POST['importe_transporte'] ?? 0)
        ];
        
        $datos['importe'] = ($datos['peso'] * $datos['precio']) + $datos['importe_transporte'];
        
        $resultado = $albaranArticuloModel->actualizar($id, $datos);
        
        if ($resultado['success']) {
            // Obtener albaran_id para recalcular totales
            $linea = $albaranArticuloModel->obtenerPorId($id);
            if ($linea) {
                $albaranModel->recalcularTotales($linea['albaran_id']);
            }
        }
        
        echo json_encode($resultado);
        break;
        
    case 'eliminar_articulo':
        if (!hasPermission('albaranes')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        // Obtener albaran_id antes de eliminar
        $linea = $albaranArticuloModel->obtenerPorId($id);
        
        $resultado = $albaranArticuloModel->eliminar($id);
        
        if ($resultado['success'] && $linea) {
            // Recalcular totales
            $albaranModel->recalcularTotales($linea['albaran_id']);
        }
        
        echo json_encode($resultado);
        break;
        
    case 'listar_clientes':
        $clientes = $clienteModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'clientes' => $clientes
        ]);
        break;
        
    case 'listar_comerciales':
        $comerciales = $comercialModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'comerciales' => $comerciales
        ]);
        break;
        
    case 'listar_mozos':
        $mozos = $mozoModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'mozos' => $mozos
        ]);
        break;
        
    case 'listar_articulos':
        $articulos = $articuloModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'articulos' => $articulos
        ]);
        break;
        
    case 'generar_numero':
        $numero = $albaranModel->generarNumeroAlbaran();
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
