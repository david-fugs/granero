<?php
require_once __DIR__ . '/../conexion.php';

class Mozo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function listar($filtros = []) {
        try {
            $sql = "SELECT * FROM mozos WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (nombre LIKE :busqueda OR numero_identificacion LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $sql .= " ORDER BY nombre ASC";
            
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
            error_log("Error en Mozo::listar - " . $e->getMessage());
            return [];
        }
    }
    
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM mozos WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (nombre LIKE :busqueda OR numero_identificacion LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $sql .= " AND activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error en Mozo::contar - " . $e->getMessage());
            return 0;
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mozos WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Mozo::obtenerPorId - " . $e->getMessage());
            return null;
        }
    }
    
    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO mozos (numero_identificacion, nombre, activo)
                VALUES (:numero_identificacion, :nombre, :activo)
            ");
            
            $stmt->execute([
                ':numero_identificacion' => $datos['numero_identificacion'],
                ':nombre' => $datos['nombre'],
                ':activo' => $datos['activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'message' => 'Mozo creado correctamente',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error en Mozo::crear - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear el mozo: ' . $e->getMessage()
            ];
        }
    }
    
    public function actualizar($id, $datos) {
        try {
            $stmt = $this->db->prepare("
                UPDATE mozos 
                SET numero_identificacion = :numero_identificacion,
                    nombre = :nombre,
                    activo = :activo
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':numero_identificacion' => $datos['numero_identificacion'],
                ':nombre' => $datos['nombre'],
                ':activo' => $datos['activo']
            ]);
            
            return [
                'success' => true,
                'message' => 'Mozo actualizado correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en Mozo::actualizar - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar el mozo: ' . $e->getMessage()
            ];
        }
    }
    
    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM mozos WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Mozo eliminado correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en Mozo::eliminar - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el mozo: ' . $e->getMessage()
            ];
        }
    }
    
    public function buscar($termino) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, numero_identificacion, nombre 
                FROM mozos 
                WHERE activo = 1 
                AND (nombre LIKE :termino OR numero_identificacion LIKE :termino)
                ORDER BY nombre ASC
                LIMIT 10
            ");
            $stmt->bindValue(':termino', '%' . $termino . '%');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Mozo::buscar - " . $e->getMessage());
            return [];
        }
    }
}
