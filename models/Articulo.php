<?php
class Articulo {
    private $db;
    private $table = 'articulos';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre_articulo, stock_disponible, cantidad_albaranes, 
                    reservado, nombre_comercial, stock_sage, activo) 
                    VALUES (:nombre_articulo, :stock_disponible, :cantidad_albaranes, 
                    :reservado, :nombre_comercial, :stock_sage, :activo)";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':nombre_articulo' => $datos['nombre_articulo'],
                ':stock_disponible' => $datos['stock_disponible'] ?? 0.00,
                ':cantidad_albaranes' => $datos['cantidad_albaranes'] ?? 0.00,
                ':reservado' => $datos['reservado'] ?? 0.00,
                ':nombre_comercial' => $datos['nombre_comercial'] ?? null,
                ':stock_sage' => $datos['stock_sage'] ?? 0.00,
                ':activo' => $datos['activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Artículo creado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear artículo: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre_articulo LIKE :search OR nombre_comercial LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['activo'])) {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $sql .= " ORDER BY nombre_articulo ASC";
            
            if (isset($filtros['limit'])) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($filtros['limit'])) {
                $stmt->bindValue(':limit', (int)$filtros['limit'], PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)($filtros['offset'] ?? 0), PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre_articulo LIKE :search OR nombre_comercial LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['activo'])) {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    nombre_articulo = :nombre_articulo,
                    stock_disponible = :stock_disponible,
                    cantidad_albaranes = :cantidad_albaranes,
                    reservado = :reservado,
                    nombre_comercial = :nombre_comercial,
                    stock_sage = :stock_sage,
                    activo = :activo
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':nombre_articulo' => $datos['nombre_articulo'],
                ':stock_disponible' => $datos['stock_disponible'],
                ':cantidad_albaranes' => $datos['cantidad_albaranes'],
                ':reservado' => $datos['reservado'],
                ':nombre_comercial' => $datos['nombre_comercial'],
                ':stock_sage' => $datos['stock_sage'],
                ':activo' => $datos['activo']
            ]);
            
            return [
                'success' => true,
                'message' => 'Artículo actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar artículo: ' . $e->getMessage()
            ];
        }
    }
    
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return [
                'success' => true,
                'message' => 'Artículo eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar artículo: ' . $e->getMessage()
            ];
        }
    }
    
    public function buscar($termino) {
        try {
            $sql = "SELECT id, nombre_articulo, nombre_comercial, stock_disponible, cantidad_albaranes, reservado, stock_sage 
                    FROM {$this->table} 
                    WHERE activo = 1 
                    AND (nombre_articulo LIKE :termino OR nombre_comercial LIKE :termino)
                    ORDER BY nombre_articulo ASC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => '%' . $termino . '%']);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function obtenerStockBajo($umbral = 50, $limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND (stock_disponible - cantidad_albaranes - reservado) < :umbral
                    ORDER BY (stock_disponible - cantidad_albaranes - reservado) ASC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':umbral', $umbral, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
