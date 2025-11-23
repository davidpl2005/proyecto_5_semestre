<?php
require_once __DIR__ . '/../config/database.php';

class MovimientoInventario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar un movimiento de inventario
     */
    public function registrar($id_producto, $tipo, $cantidad, $descripcion, $usuario_id = null) {
        try {
            $sql = "INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, descripcion, usuario_id) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_producto, $tipo, $cantidad, $descripcion, $usuario_id]);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::registrar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los movimientos con información de productos
     */
    public function getAll($limite = null) {
        try {
            $sql = "SELECT m.*, p.nombre as nombre_producto, p.imagen,
                    u.nombre as nombre_usuario
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.id_producto = p.id_producto
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                    ORDER BY m.fecha_movimiento DESC";
            
            if ($limite) {
                $sql .= " LIMIT " . intval($limite);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener movimientos de un producto específico
     */
    public function getByProducto($id_producto, $limite = null) {
        try {
            $sql = "SELECT m.*, p.nombre as nombre_producto,
                    u.nombre as nombre_usuario
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.id_producto = p.id_producto
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                    WHERE m.id_producto = ?
                    ORDER BY m.fecha_movimiento DESC";
            
            if ($limite) {
                $sql .= " LIMIT " . intval($limite);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_producto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::getByProducto(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener movimientos por tipo
     */
    public function getByTipo($tipo, $limite = null) {
        try {
            $sql = "SELECT m.*, p.nombre as nombre_producto, p.imagen,
                    u.nombre as nombre_usuario
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.id_producto = p.id_producto
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                    WHERE m.tipo_movimiento = ?
                    ORDER BY m.fecha_movimiento DESC";
            
            if ($limite) {
                $sql .= " LIMIT " . intval($limite);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::getByTipo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener movimientos por rango de fechas
     */
    public function getByFechas($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT m.*, p.nombre as nombre_producto,
                    u.nombre as nombre_usuario
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.id_producto = p.id_producto
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                    WHERE DATE(m.fecha_movimiento) BETWEEN ? AND ?
                    ORDER BY m.fecha_movimiento DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::getByFechas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de movimientos
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_movimientos,
                    SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad ELSE 0 END) as total_entradas,
                    SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad ELSE 0 END) as total_salidas,
                    COUNT(CASE WHEN tipo_movimiento = 'entrada' THEN 1 END) as num_entradas,
                    COUNT(CASE WHEN tipo_movimiento = 'salida' THEN 1 END) as num_salidas
                    FROM movimientos_inventario";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::getEstadisticas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener últimos movimientos (para dashboard)
     */
    public function getUltimos($limite = 10) {
        return $this->getAll($limite);
    }

    /**
     * Eliminar un movimiento
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM movimientos_inventario WHERE id_movimiento = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error en MovimientoInventario::delete(): " . $e->getMessage());
            return false;
        }
    }
}