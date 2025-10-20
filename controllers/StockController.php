<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Articulo.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$articuloModel = new Articulo();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'search' => $_GET['busqueda'] ?? '',
            'activo' => $_GET['activo'] ?? 1,
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $articulos = $articuloModel->listar($filtros);
        $total = $articuloModel->contar($filtros);
        
        // Calcular stock total para cada artículo
        foreach ($articulos as &$articulo) {
            $articulo['stock_total'] = $articulo['stock_disponible'] + $articulo['cantidad_albaranes'] + $articulo['reservado'];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $articulos,
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
        $articulo = $articuloModel->obtenerPorId($id);
        
        if ($articulo) {
            echo json_encode([
                'success' => true,
                'data' => $articulo
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Artículo no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear artículos'
            ]);
            exit;
        }
        
        $datos = [
            'nombre_articulo' => sanitize($_POST['nombre_articulo'] ?? ''),
            'nombre_comercial' => sanitize($_POST['nombre_comercial'] ?? ''),
            'stock_disponible' => floatval($_POST['stock_disponible'] ?? 0),
            'cantidad_albaranes' => floatval($_POST['cantidad_albaranes'] ?? 0),
            'reservado' => floatval($_POST['reservado'] ?? 0),
            'stock_sage' => floatval($_POST['stock_sage'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $articuloModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar artículos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'nombre_articulo' => sanitize($_POST['nombre_articulo'] ?? ''),
            'nombre_comercial' => sanitize($_POST['nombre_comercial'] ?? ''),
            'stock_disponible' => floatval($_POST['stock_disponible'] ?? 0),
            'cantidad_albaranes' => floatval($_POST['cantidad_albaranes'] ?? 0),
            'reservado' => floatval($_POST['reservado'] ?? 0),
            'stock_sage' => floatval($_POST['stock_sage'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $articuloModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('stock')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar artículos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $articuloModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'buscar':
        $termino = $_GET['termino'] ?? '';
        $resultados = $articuloModel->buscar($termino);
        echo json_encode([
            'success' => true,
            'data' => $resultados
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}
