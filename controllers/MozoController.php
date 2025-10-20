<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Mozo.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$mozoModel = new Mozo();

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
        
        $mozos = $mozoModel->listar($filtros);
        $total = $mozoModel->contar($filtros);
        
        echo json_encode([
            'success' => true,
            'data' => $mozos,
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
        $mozo = $mozoModel->obtenerPorId($id);
        
        if ($mozo) {
            echo json_encode([
                'success' => true,
                'data' => $mozo
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Mozo no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('mozos')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear mozos'
            ]);
            exit;
        }
        
        $datos = [
            'numero_identificacion' => sanitize($_POST['numero_identificacion'] ?? ''),
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $mozoModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('mozos')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar mozos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'numero_identificacion' => sanitize($_POST['numero_identificacion'] ?? ''),
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $resultado = $mozoModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('mozos')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar mozos'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $resultado = $mozoModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    case 'buscar':
        $termino = $_GET['termino'] ?? '';
        $resultados = $mozoModel->buscar($termino);
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
