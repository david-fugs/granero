<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Usuario.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$usuarioModel = new Usuario();

switch ($action) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;
        $offset = ($pagina - 1) * $limite;
        
        $filtros = [
            'search' => $_GET['busqueda'] ?? '',
            'tipo_usuario' => $_GET['tipo'] ?? '',
            'activo' => $_GET['activo'] ?? '',
            'limit' => $limite,
            'offset' => $offset
        ];
        
        $usuarios = $usuarioModel->listar($filtros);
        $total = $usuarioModel->contar($filtros);
        
        echo json_encode([
            'success' => true,
            'data' => $usuarios,
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
        $usuario = $usuarioModel->obtenerPorId($id);
        
        if ($usuario) {
            // No enviar password
            unset($usuario['password']);
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
        break;
        
    case 'crear':
        if (!hasPermission('usuarios')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para crear usuarios'
            ]);
            exit;
        }
        
        $datos = [
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'tipo_usuario' => $_POST['tipo_usuario'] ?? 'visualizador',
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        if (empty($datos['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'La contraseña es obligatoria'
            ]);
            exit;
        }
        
        $resultado = $usuarioModel->crear($datos);
        echo json_encode($resultado);
        break;
        
    case 'actualizar':
        if (!hasPermission('usuarios')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para actualizar usuarios'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $datos = [
            'nombre' => sanitize($_POST['nombre'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'tipo_usuario' => $_POST['tipo_usuario'] ?? 'visualizador',
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Solo actualizar password si se proporciona
        if (!empty($_POST['password'])) {
            $datos['password'] = $_POST['password'];
        }
        
        $resultado = $usuarioModel->actualizar($id, $datos);
        echo json_encode($resultado);
        break;
        
    case 'eliminar':
        if (!hasPermission('usuarios')) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para eliminar usuarios'
            ]);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        // No permitir eliminar el propio usuario
        if ($id == $_SESSION['user_id']) {
            echo json_encode([
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario'
            ]);
            exit;
        }
        
        $resultado = $usuarioModel->eliminar($id);
        echo json_encode($resultado);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}
