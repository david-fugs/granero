<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/StockPartida.php';
require_once __DIR__ . '/../models/Articulo.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$stockPartidaModel = new StockPartida();
$articuloModel = new Articulo();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'busqueda' => $_GET['busqueda'] ?? '',
            'articulo_id' => $_GET['articulo_id'] ?? '',
            'activo' => $_GET['activo'] ?? 1,
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $partidas = $stockPartidaModel->listar($filtros);
        $total = $stockPartidaModel->contar($filtros);
        
        // Calcular stock total para cada partida
        foreach ($partidas as &$partida) {
            $partida['stock_total'] = $partida['stock_disponible'] + $partida['cantidad_albaranes'] + $partida['reservado'];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $partidas,
            'paginacion' => [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'total_paginas' => ceil($total / $limite)
            ]
        ]);
        break;
        
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $partida = $stockPartidaModel->obtenerPorId($id);
        
        if ($partida) {
            echo json_encode([
                'success' => true,
                'data' => $partida
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Partida no encontrada'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear partidas'
            ]);
            exit;
        }
        
        $datos = [
            'articulo_id' => intval($_POST['articulo_id'] ?? 0),
            'partida' => sanitize($_POST['partida'] ?? ''),
            'fecha_partida' => $_POST['fecha_partida'] ?? date('Y-m-d'),
            'stock_disponible' => floatval($_POST['stock_disponible'] ?? 0),
            'cantidad_albaranes' => floatval($_POST['cantidad_albaranes'] ?? 0),
            'reservado' => floatval($_POST['reservado'] ?? 0),
            'nombre_comercial' => sanitize($_POST['nombre_comercial'] ?? ''),
            'stock_sage' => floatval($_POST['stock_sage'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        if ($datos['articulo_id'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe seleccionar un artículo'
            ]);
            exit;
        }
        
        $resultado = $stockPartidaModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar partidas'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'articulo_id' => intval($_POST['articulo_id'] ?? 0),
            'partida' => sanitize($_POST['partida'] ?? ''),
            'fecha_partida' => $_POST['fecha_partida'] ?? date('Y-m-d'),
            'stock_disponible' => floatval($_POST['stock_disponible'] ?? 0),
            'cantidad_albaranes' => floatval($_POST['cantidad_albaranes'] ?? 0),
            'reservado' => floatval($_POST['reservado'] ?? 0),
            'nombre_comercial' => sanitize($_POST['nombre_comercial'] ?? ''),
            'stock_sage' => floatval($_POST['stock_sage'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $stockPartidaModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar partidas'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $stockPartidaModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'listar_articulos':
        // Para el select de artículos en el modal
        $articulos = $articuloModel->listar(['activo' => 1]);
        echo json_encode([
            'success' => true,
            'data' => $articulos
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}
