<?php
require_once __DIR__ . '/../conexion.php';

class Movimiento {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT m.*, a.nombre_articulo, u.nombre as usuario_nombre
                    FROM movimientos m
                    INNER JOIN articulos a ON m.articulo_id = a.id
                    LEFT JOIN usuarios u ON m.usuario_id = u.id
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.nombre_articulo LIKE :busqueda OR m.observaciones LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (!empty($filtros['articulo_id'])) {
                $sql .= " AND m.articulo_id = :articulo_id";
                $params[':articulo_id'] = $filtros['articulo_id'];
            }
            
            if (!empty($filtros['tipo_movimiento'])) {
                $sql .= " AND m.tipo_movimiento = :tipo_movimiento";
                $params[':tipo_movimiento'] = $filtros['tipo_movimiento'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(m.fecha_movimiento) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(m.fecha_movimiento) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql .= " ORDER BY m.fecha_movimiento DESC";
            
            if (isset($filtros['limit']) && isset($filtros['offset'])) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($filtros['limit'])) {
                $stmt->bindValue(':limit', (int)$filtros['limit'], PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$filtros['offset'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Movimiento::listar - " . $e->getMessage());
            return [];
        }
    }
    
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM movimientos m
                    INNER JOIN articulos a ON m.articulo_id = a.id
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.nombre_articulo LIKE :busqueda OR m.observaciones LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (!empty($filtros['articulo_id'])) {
                $sql .= " AND m.articulo_id = :articulo_id";
                $params[':articulo_id'] = $filtros['articulo_id'];
            }
            
            if (!empty($filtros['tipo_movimiento'])) {
                $sql .= " AND m.tipo_movimiento = :tipo_movimiento";
                $params[':tipo_movimiento'] = $filtros['tipo_movimiento'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(m.fecha_movimiento) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(m.fecha_movimiento) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error en Movimiento::contar - " . $e->getMessage());
            return 0;
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, a.nombre_articulo, u.nombre as usuario_nombre
                FROM movimientos m
                INNER JOIN articulos a ON m.articulo_id = a.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Movimiento::obtenerPorId - " . $e->getMessage());
            return null;
        }
    }
    
    public function crear($datos) {
        try {
            $this->db->beginTransaction();
            
            // Crear movimiento
            $stmt = $this->db->prepare("
                INSERT INTO movimientos (
                    articulo_id, stock_disponible, cantidad_albaranes, reservado,
                    nombre_comercial, stock_sage, tipo_movimiento, cantidad,
                    observaciones, usuario_id
                ) VALUES (
                    :articulo_id, :stock_disponible, :cantidad_albaranes, :reservado,
                    :nombre_comercial, :stock_sage, :tipo_movimiento, :cantidad,
                    :observaciones, :usuario_id
                )
            ");
            
            $stmt->execute([
                ':articulo_id' => $datos['articulo_id'],
                ':stock_disponible' => $datos['stock_disponible'] ?? 0,
                ':cantidad_albaranes' => $datos['cantidad_albaranes'] ?? 0,
                ':reservado' => $datos['reservado'] ?? 0,
                ':nombre_comercial' => $datos['nombre_comercial'] ?? null,
                ':stock_sage' => $datos['stock_sage'] ?? 0,
                ':tipo_movimiento' => $datos['tipo_movimiento'],
                ':cantidad' => $datos['cantidad'],
                ':observaciones' => $datos['observaciones'] ?? null,
                ':usuario_id' => $datos['usuario_id']
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Movimiento registrado correctamente',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Movimiento::crear - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al registrar el movimiento: ' . $e->getMessage()
            ];
        }
    }
    
    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM movimientos WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Movimiento eliminado correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en Movimiento::eliminar - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ];
        }
    }
}
