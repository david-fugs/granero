<?php
require_once __DIR__ . '/../conexion.php';

class AlbaranArticulo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Listar artículos de un albarán
     */
    public function listarPorAlbaran($albaran_id) {
        try {
            $sql = "SELECT aa.*, a.nombre_articulo
                    FROM albaran_articulos aa
                    INNER JOIN articulos a ON aa.articulo_id = a.id
                    WHERE aa.albaran_id = :albaran_id
                    ORDER BY aa.id ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':albaran_id', $albaran_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al listar artículos del albarán: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener artículo por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT aa.*, a.nombre_articulo
                    FROM albaran_articulos aa
                    INNER JOIN articulos a ON aa.articulo_id = a.id
                    WHERE aa.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener artículo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear línea de artículo
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO albaran_articulos (
                        albaran_id, articulo_id, partida, unidades, 
                        peso, precio, importe_transporte, importe
                    ) VALUES (
                        :albaran_id, :articulo_id, :partida, :unidades,
                        :peso, :precio, :importe_transporte, :importe
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':albaran_id', $datos['albaran_id'], PDO::PARAM_INT);
            $stmt->bindValue(':articulo_id', $datos['articulo_id'], PDO::PARAM_INT);
            $stmt->bindValue(':partida', $datos['partida']);
            $stmt->bindValue(':unidades', $datos['unidades']);
            $stmt->bindValue(':peso', $datos['peso']);
            $stmt->bindValue(':precio', $datos['precio']);
            $stmt->bindValue(':importe_transporte', $datos['importe_transporte']);
            $stmt->bindValue(':importe', $datos['importe']);
            
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Artículo agregado correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            error_log("Error al crear artículo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al agregar el artículo'
            ];
        }
    }
    
    /**
     * Actualizar línea de artículo
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE albaran_articulos SET
                        articulo_id = :articulo_id,
                        partida = :partida,
                        unidades = :unidades,
                        peso = :peso,
                        precio = :precio,
                        importe_transporte = :importe_transporte,
                        importe = :importe
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':articulo_id', $datos['articulo_id'], PDO::PARAM_INT);
            $stmt->bindValue(':partida', $datos['partida']);
            $stmt->bindValue(':unidades', $datos['unidades']);
            $stmt->bindValue(':peso', $datos['peso']);
            $stmt->bindValue(':precio', $datos['precio']);
            $stmt->bindValue(':importe_transporte', $datos['importe_transporte']);
            $stmt->bindValue(':importe', $datos['importe']);
            
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Artículo actualizado correctamente'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al actualizar artículo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar el artículo'
            ];
        }
    }
    
    /**
     * Eliminar línea de artículo
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM albaran_articulos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Artículo eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al eliminar artículo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el artículo'
            ];
        }
    }
}
