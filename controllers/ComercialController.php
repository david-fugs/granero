<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Comercial.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$comercialModel = new Comercial();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'busqueda' => $_GET['busqueda'] ?? '',
            'activo' => $_GET['activo'] ?? '',
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $comerciales = $comercialModel->listar($filtros);
        $total = $comercialModel->contar($filtros);
        
        echo json_encode([
            'success' => true,
            'data' => $comerciales,
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
        $comercial = $comercialModel->obtenerPorId($id);
        
        if ($comercial) {
            echo json_encode([
                'success' => true,
                'data' => $comercial
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Comercial no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('comerciales')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear comerciales'
            ]);
            exit;
        }
        
        $datos = [
            'numero_identificacion' => sanitize($_POST['numero_identificacion'] ?? ''),
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $comercialModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('comerciales')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar comerciales'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'numero_identificacion' => sanitize($_POST['numero_identificacion'] ?? ''),
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $comercialModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('comerciales')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar comerciales'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $comercialModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'buscar':
        $termino = $_GET['termino'] ?? '';
        $resultados = $comercialModel->buscar($termino);
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
