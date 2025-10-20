<?php
class Usuario {
    private $db;
    private $table = 'usuarios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Crear nuevo usuario
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, email, password, tipo_usuario, activo) 
                    VALUES (:nombre, :email, :password, :tipo_usuario, :activo)";
            
            $stmt = $this->db->prepare($sql);
            
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':password' => $passwordHash,
                ':tipo_usuario' => $datos['tipo_usuario'] ?? 'visualizador',
                ':activo' => $datos['activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Usuario creado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener usuario por ID
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT id, nombre, email, tipo_usuario, activo, fecha_creacion 
                    FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Obtener usuario por email
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Listar todos los usuarios
    public function listar($filtros = []) {
        try {
            $sql = "SELECT id, nombre, email, tipo_usuario, activo, fecha_creacion 
                    FROM {$this->table} WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre LIKE :search OR email LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['tipo_usuario'])) {
                $sql .= " AND tipo_usuario = :tipo_usuario";
                $params[':tipo_usuario'] = $filtros['tipo_usuario'];
            }
            
            if (isset($filtros['activo'])) {
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
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Contar usuarios
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['search'])) {
                $sql .= " AND (nombre LIKE :search OR email LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }
            
            if (isset($filtros['tipo_usuario'])) {
                $sql .= " AND tipo_usuario = :tipo_usuario";
                $params[':tipo_usuario'] = $filtros['tipo_usuario'];
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
    
    // Actualizar usuario
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    nombre = :nombre,
                    email = :email,
                    tipo_usuario = :tipo_usuario,
                    activo = :activo";
            
            $params = [
                ':id' => $id,
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':tipo_usuario' => $datos['tipo_usuario'],
                ':activo' => $datos['activo']
            ];
            
            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar usuario
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    // Verificar credenciales para login
    public function verificarCredenciales($email, $password) {
        try {
            $usuario = $this->obtenerPorEmail($email);
            
            if (!$usuario) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }
            
            if (!$usuario['activo']) {
                return [
                    'success' => false,
                    'message' => 'Usuario inactivo. Contacte al administrador'
                ];
            }
            
            if (password_verify($password, $usuario['password'])) {
                return [
                    'success' => true,
                    'usuario' => [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'email' => $usuario['email'],
                        'tipo_usuario' => $usuario['tipo_usuario']
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar credenciales'
            ];
        }
    }
}
?>
