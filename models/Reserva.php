<?php
class Reserva {
    private $db;
    private $table = 'reservas';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (numero_reserva, numero_lineas, fecha, cliente_id, 
                    estado, comercial_id, transportista, plataforma_carga, faltan_precios, prepago, numero_albaran) 
                    VALUES (:numero_reserva, :numero_lineas, :fecha, :cliente_id, 
                    :estado, :comercial_id, :transportista, :plataforma_carga, :faltan_precios, :prepago, :numero_albaran)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':numero_reserva' => $datos['numero_reserva'],
                ':numero_lineas' => $datos['numero_lineas'] ?? 0,
                ':fecha' => $datos['fecha'],
                ':cliente_id' => $datos['cliente_id'],
                ':estado' => $datos['estado'] ?? 'reservado',
                ':comercial_id' => $datos['comercial_id'] ?? null,
                ':transportista' => $datos['transportista'] ?? null,
                ':plataforma_carga' => $datos['plataforma_carga'] ?? null,
                ':faltan_precios' => $datos['faltan_precios'] ?? 'no',
                ':prepago' => $datos['prepago'] ?? 'no',
                ':numero_albaran' => $datos['numero_albaran'] ?? null
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Reserva creada exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear reserva: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT r.*, c.nombre_cliente, com.nombre as nombre_comercial 
                    FROM {$this->table} r
                    LEFT JOIN clientes c ON r.cliente_id = c.id
                    LEFT JOIN comerciales com ON r.comercial_id = com.id
                    WHERE r.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT r.*, c.nombre_cliente, com.nombre as nombre_comercial 
                    FROM {$this->table} r
                    LEFT JOIN clientes c ON r.cliente_id = c.id
                    LEFT JOIN comerciales com ON r.comercial_id = com.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (r.numero_reserva LIKE :search OR c.nombre_cliente LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['estado'])) {
                $sql .= " AND r.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (isset($filtros['order'])) {
                $sql .= " ORDER BY " . $filtros['order'];
            } else {
                $sql .= " ORDER BY r.fecha DESC";
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
    
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} r
                    LEFT JOIN clientes c ON r.cliente_id = c.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (r.numero_reserva LIKE :search OR c.nombre_cliente LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['estado'])) {
                $sql .= " AND r.estado = :estado";
                $params[':estado'] = $filtros['estado'];
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
                    numero_reserva = :numero_reserva,
                    numero_lineas = :numero_lineas,
                    fecha = :fecha,
                    cliente_id = :cliente_id,
                    estado = :estado,
                    comercial_id = :comercial_id,
                    transportista = :transportista,
                    plataforma_carga = :plataforma_carga,
                    faltan_precios = :faltan_precios,
                    prepago = :prepago,
                    numero_albaran = :numero_albaran
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':numero_reserva' => $datos['numero_reserva'],
                ':numero_lineas' => $datos['numero_lineas'],
                ':fecha' => $datos['fecha'],
                ':cliente_id' => $datos['cliente_id'],
                ':estado' => $datos['estado'],
                ':comercial_id' => $datos['comercial_id'],
                ':transportista' => $datos['transportista'],
                ':plataforma_carga' => $datos['plataforma_carga'],
                ':faltan_precios' => $datos['faltan_precios'],
                ':prepago' => $datos['prepago'],
                ':numero_albaran' => $datos['numero_albaran']
            ]);
            
            return [
                'success' => true,
                'message' => 'Reserva actualizada exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar reserva: ' . $e->getMessage()
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
                'message' => 'Reserva eliminada exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar reserva: ' . $e->getMessage()
            ];
        }
    }
}
?>
