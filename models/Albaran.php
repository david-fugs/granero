<?php
class Albaran {
    private $db;
    private $table = 'albaranes';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (numero_albaran, fecha, multipedido, comercial_id, 
                    mozo_id, cliente_id, faltan_precios, preparado, unificado, prepago, estado) 
                    VALUES (:numero_albaran, :fecha, :multipedido, :comercial_id, 
                    :mozo_id, :cliente_id, :faltan_precios, :preparado, :unificado, :prepago, :estado)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':numero_albaran' => $datos['numero_albaran'],
                ':fecha' => $datos['fecha'],
                ':multipedido' => $datos['multipedido'] ?? null,
                ':comercial_id' => $datos['comercial_id'] ?? null,
                ':mozo_id' => $datos['mozo_id'] ?? null,
                ':cliente_id' => $datos['cliente_id'],
                ':faltan_precios' => $datos['faltan_precios'] ?? 0,
                ':preparado' => $datos['preparado'] ?? 0,
                ':unificado' => $datos['unificado'] ?? 0,
                ':prepago' => $datos['prepago'] ?? 0,
                ':estado' => $datos['estado'] ?? 'pendiente'
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Albarán creado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear albarán: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT a.*, c.nombre_cliente, com.nombre as nombre_comercial, m.nombre as nombre_mozo 
                    FROM {$this->table} a
                    LEFT JOIN clientes c ON a.cliente_id = c.id
                    LEFT JOIN comerciales com ON a.comercial_id = com.id
                    LEFT JOIN mozos m ON a.mozo_id = m.id
                    WHERE a.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT a.*, c.nombre_cliente, com.nombre as nombre_comercial, m.nombre as nombre_mozo 
                    FROM {$this->table} a
                    LEFT JOIN clientes c ON a.cliente_id = c.id
                    LEFT JOIN comerciales com ON a.comercial_id = com.id
                    LEFT JOIN mozos m ON a.mozo_id = m.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (a.numero_albaran LIKE :search OR c.nombre_cliente LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['estado'])) {
                $sql .= " AND a.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (isset($filtros['order'])) {
                $sql .= " ORDER BY " . $filtros['order'];
            } else {
                $sql .= " ORDER BY a.fecha DESC";
            }
            
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
    
    public function contarPorEstado($estados) {
        try {
            $placeholders = implode(',', array_fill(0, count($estados), '?'));
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado IN ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($estados);
            
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    numero_albaran = :numero_albaran,
                    fecha = :fecha,
                    multipedido = :multipedido,
                    comercial_id = :comercial_id,
                    mozo_id = :mozo_id,
                    cliente_id = :cliente_id,
                    faltan_precios = :faltan_precios,
                    preparado = :preparado,
                    unificado = :unificado,
                    prepago = :prepago,
                    estado = :estado
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':numero_albaran' => $datos['numero_albaran'],
                ':fecha' => $datos['fecha'],
                ':multipedido' => $datos['multipedido'],
                ':comercial_id' => $datos['comercial_id'],
                ':mozo_id' => $datos['mozo_id'],
                ':cliente_id' => $datos['cliente_id'],
                ':faltan_precios' => $datos['faltan_precios'],
                ':preparado' => $datos['preparado'],
                ':unificado' => $datos['unificado'],
                ':prepago' => $datos['prepago'],
                ':estado' => $datos['estado']
            ]);
            
            return [
                'success' => true,
                'message' => 'Albarán actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar albarán: ' . $e->getMessage()
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
                'message' => 'Albarán eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar albarán: ' . $e->getMessage()
            ];
        }
    }
}
?>
