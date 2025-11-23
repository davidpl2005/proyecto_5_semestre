<?php
require_once __DIR__ . '/../config/database.php';

class Pago {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear un nuevo pago
     * @param int $id_pedido ID del pedido
     * @param string $metodo_pago Método de pago (efectivo, tarjeta, transferencia)
     * @param float $monto Monto del pago
     * @param string $estado Estado del pago (pendiente, completado, cancelado)
     * @return int|false ID del pago creado o false si falla
     */
    public function create($id_pedido, $metodo_pago, $monto, $estado = 'completado') {
        try {
            // Generar referencia única
            $referencia = 'PAG-' . date('Ymd') . '-' . str_pad($id_pedido, 6, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO pagos (id_pedido, metodo_pago, monto, estado_pago, referencia) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$id_pedido, $metodo_pago, $monto, $estado, $referencia])) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error en Pago::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener un pago por su ID
     */
    public function findById($id) {
        try {
            $sql = "SELECT p.*, ped.total as total_pedido 
                    FROM pagos p
                    INNER JOIN pedidos ped ON p.id_pedido = ped.id_pedido
                    WHERE p.id_pago = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pago::findById(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener pago por ID de pedido
     */
    public function findByPedido($id_pedido) {
        try {
            $sql = "SELECT * FROM pagos WHERE id_pedido = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pago::findByPedido(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los pagos de un usuario
     */
    public function getByUsuario($id_usuario) {
        try {
            $sql = "SELECT p.*, ped.total, ped.fecha_pedido 
                    FROM pagos p
                    INNER JOIN pedidos ped ON p.id_pedido = ped.id_pedido
                    WHERE ped.id_usuario = ?
                    ORDER BY p.fecha_pago DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pago::getByUsuario(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los pagos (para admin)
     */
    public function getAll() {
        try {
            $sql = "SELECT p.*, ped.id_pedido, ped.total, u.nombre as nombre_usuario, u.correo
                    FROM pagos p
                    INNER JOIN pedidos ped ON p.id_pedido = ped.id_pedido
                    INNER JOIN usuarios u ON ped.id_usuario = u.id_usuario
                    ORDER BY p.fecha_pago DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pago::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar estado del pago
     */
    public function updateEstado($id, $estado) {
        try {
            $sql = "UPDATE pagos SET estado_pago = ? WHERE id_pago = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch(PDOException $e) {
            error_log("Error en Pago::updateEstado(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un pedido tiene pago
     */
    public function pedidoTienePago($id_pedido) {
        try {
            $sql = "SELECT COUNT(*) as total FROM pagos WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch(PDOException $e) {
            error_log("Error en Pago::pedidoTienePago(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de pagos
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(CASE WHEN estado_pago = 'completado' THEN 1 ELSE 0 END) as completados,
                    SUM(CASE WHEN estado_pago = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN metodo_pago = 'efectivo' THEN 1 ELSE 0 END) as efectivo,
                    SUM(CASE WHEN metodo_pago = 'tarjeta' THEN 1 ELSE 0 END) as tarjeta,
                    SUM(CASE WHEN metodo_pago = 'transferencia' THEN 1 ELSE 0 END) as transferencia,
                    SUM(CASE WHEN estado_pago = 'completado' THEN monto ELSE 0 END) as total_recaudado
                    FROM pagos";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pago::getEstadisticas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar un pago
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM pagos WHERE id_pago = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error en Pago::delete(): " . $e->getMessage());
            return false;
        }
    }
}