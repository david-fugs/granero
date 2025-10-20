<?php
class Cliente {
    private $db;
    private $table = 'clientes';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Crear nuevo cliente
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (codigo_cliente, nombre_cliente, nif, paga_transporte, 
                    importe_riesgo, deuda, plat, carga, activo) 
                    VALUES (:codigo_cliente, :nombre_cliente, :nif, :paga_transporte, 
                    :importe_riesgo, :deuda, :plat, :carga, :activo)";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':codigo_cliente' => $datos['codigo_cliente'],
                ':nombre_cliente' => $datos['nombre_cliente'],
                ':nif' => $datos['nif'] ?? null,
                ':paga_transporte' => $datos['paga_transporte'] ?? 'no',
                ':importe_riesgo' => $datos['importe_riesgo'] ?? 0.00,
                ':deuda' => $datos['deuda'] ?? 0.00,
                ':plat' => $datos['plat'] ?? null,
                ':carga' => $datos['carga'] ?? null,
                ':activo' => $datos['activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Cliente creado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener cliente por ID
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
    
    // Listar todos los clientes
    public function listar($filtros = []) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre_cliente LIKE :search OR codigo_cliente LIKE :search OR nif LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['activo'])) {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            if (isset($filtros['paga_transporte'])) {
                $sql .= " AND paga_transporte = :paga_transporte";
                $params[':paga_transporte'] = $filtros['paga_transporte'];
            }
            
            $sql .= " ORDER BY nombre_cliente ASC";
            
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
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Contar clientes
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre_cliente LIKE :search OR codigo_cliente LIKE :search OR nif LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['activo'])) {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            if (isset($filtros['paga_transporte'])) {
                $sql .= " AND paga_transporte = :paga_transporte";
                $params[':paga_transporte'] = $filtros['paga_transporte'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    // Actualizar cliente
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    codigo_cliente = :codigo_cliente,
                    nombre_cliente = :nombre_cliente,
                    nif = :nif,
                    paga_transporte = :paga_transporte,
                    importe_riesgo = :importe_riesgo,
                    deuda = :deuda,
                    plat = :plat,
                    carga = :carga,
                    activo = :activo
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':id' => $id,
                ':codigo_cliente' => $datos['codigo_cliente'],
                ':nombre_cliente' => $datos['nombre_cliente'],
                ':nif' => $datos['nif'] ?? null,
                ':paga_transporte' => $datos['paga_transporte'],
                ':importe_riesgo' => $datos['importe_riesgo'],
                ':deuda' => $datos['deuda'],
                ':plat' => $datos['plat'] ?? null,
                ':carga' => $datos['carga'] ?? null,
                ':activo' => $datos['activo']
            ]);
            
            return [
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar cliente
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return [
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ];
        }
    }
    
    // Buscar clientes para autocomplete
    public function buscar($termino) {
        try {
            $sql = "SELECT id, codigo_cliente, nombre_cliente, nif 
                    FROM {$this->table} 
                    WHERE activo = 1 
                    AND (nombre_cliente LIKE :termino OR codigo_cliente LIKE :termino OR nif LIKE :termino)
                    ORDER BY nombre_cliente ASC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => '%' . $termino . '%']);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
