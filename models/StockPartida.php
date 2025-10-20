<?php
require_once __DIR__ . '/../conexion.php';

class StockPartida {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT sp.*, a.nombre_articulo 
                    FROM stock_partidas sp
                    INNER JOIN articulos a ON sp.articulo_id = a.id
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.nombre_articulo LIKE :busqueda OR sp.partida LIKE :busqueda OR sp.nombre_comercial LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (!empty($filtros['articulo_id'])) {
                $sql .= " AND sp.articulo_id = :articulo_id";
                $params[':articulo_id'] = $filtros['articulo_id'];
            }
            
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $sql .= " AND sp.activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $sql .= " ORDER BY sp.fecha_partida DESC, a.nombre_articulo ASC";
            
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
            error_log("Error en StockPartida::listar - " . $e->getMessage());
            return [];
        }
    }
    
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM stock_partidas sp
                    INNER JOIN articulos a ON sp.articulo_id = a.id
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.nombre_articulo LIKE :busqueda OR sp.partida LIKE :busqueda OR sp.nombre_comercial LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (!empty($filtros['articulo_id'])) {
                $sql .= " AND sp.articulo_id = :articulo_id";
                $params[':articulo_id'] = $filtros['articulo_id'];
            }
            
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $sql .= " AND sp.activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error en StockPartida::contar - " . $e->getMessage());
            return 0;
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, a.nombre_articulo 
                FROM stock_partidas sp
                INNER JOIN articulos a ON sp.articulo_id = a.id
                WHERE sp.id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en StockPartida::obtenerPorId - " . $e->getMessage());
            return null;
        }
    }
    
    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock_partidas (
                    articulo_id, partida, fecha_partida, stock_disponible, 
                    cantidad_albaranes, reservado, nombre_comercial, stock_sage, activo
                ) VALUES (
                    :articulo_id, :partida, :fecha_partida, :stock_disponible,
                    :cantidad_albaranes, :reservado, :nombre_comercial, :stock_sage, :activo
                )
            ");
            
            $stmt->execute([
                ':articulo_id' => $datos['articulo_id'],
                ':partida' => $datos['partida'],
                ':fecha_partida' => $datos['fecha_partida'],
                ':stock_disponible' => $datos['stock_disponible'] ?? 0.00,
                ':cantidad_albaranes' => $datos['cantidad_albaranes'] ?? 0.00,
                ':reservado' => $datos['reservado'] ?? 0.00,
                ':nombre_comercial' => $datos['nombre_comercial'] ?? null,
                ':stock_sage' => $datos['stock_sage'] ?? 0.00,
                ':activo' => $datos['activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'message' => 'Partida creada correctamente',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error en StockPartida::crear - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear la partida: ' . $e->getMessage()
            ];
        }
    }
    
    public function actualizar($id, $datos) {
        try {
            $stmt = $this->db->prepare("
                UPDATE stock_partidas 
                SET articulo_id = :articulo_id,
                    partida = :partida,
                    fecha_partida = :fecha_partida,
                    stock_disponible = :stock_disponible,
                    cantidad_albaranes = :cantidad_albaranes,
                    reservado = :reservado,
                    nombre_comercial = :nombre_comercial,
                    stock_sage = :stock_sage,
                    activo = :activo
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':articulo_id' => $datos['articulo_id'],
                ':partida' => $datos['partida'],
                ':fecha_partida' => $datos['fecha_partida'],
                ':stock_disponible' => $datos['stock_disponible'],
                ':cantidad_albaranes' => $datos['cantidad_albaranes'],
                ':reservado' => $datos['reservado'],
                ':nombre_comercial' => $datos['nombre_comercial'],
                ':stock_sage' => $datos['stock_sage'],
                ':activo' => $datos['activo']
            ]);
            
            return [
                'success' => true,
                'message' => 'Partida actualizada correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en StockPartida::actualizar - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar la partida: ' . $e->getMessage()
            ];
        }
    }
    
    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM stock_partidas WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Partida eliminada correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en StockPartida::eliminar - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar la partida: ' . $e->getMessage()
            ];
        }
    }
}
