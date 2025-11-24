<?php
require_once __DIR__ . '/../config/database.php';

class Pedido {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear un nuevo pedido
     * @param int $id_usuario ID del usuario que hace el pedido
     * @param float $total Total del pedido
     * @param string $estado Estado inicial del pedido (por defecto: 'pendiente')
     * @return int|false ID del pedido creado o false si falla
     */
    public function create($id_usuario, $total, $estado = 'pendiente') {
        try {
            $sql = "INSERT INTO pedidos (id_usuario, total, estado, fecha_pedido) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$id_usuario, $total, $estado])) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error en Pedido::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener un pedido por su ID
     */
    public function findById($id) {
        try {
            $sql = "SELECT p.*, u.nombre as nombre_usuario, u.correo 
                    FROM pedidos p
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE p.id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pedido::findById(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los pedidos de un usuario
     */
    public function getByUsuario($id_usuario) {
        try {
            $sql = "SELECT * FROM pedidos 
                    WHERE id_usuario = ? 
                    ORDER BY fecha_pedido DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pedido::getByUsuario(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los pedidos (para admin)
     */
    public function getAll() {
        try {
            $sql = "SELECT p.*, u.nombre as nombre_usuario, u.correo 
                    FROM pedidos p
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    ORDER BY p.fecha_pedido DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pedido::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar el estado de un pedido
     */
    public function updateEstado($id, $estado) {
        try {
            // Verificar si el pedido ya está entregado
            $pedido = $this->findById($id);
            if ($pedido && $pedido['estado'] === 'entregado') {
                error_log("Intento de modificar pedido entregado #$id");
                return false;
            }

            $sql = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch(PDOException $e) {
            error_log("Error en Pedido::updateEstado(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un pedido completamente (con todas sus relaciones)
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // 1. Eliminar facturas asociadas
            $sqlFacturas = "DELETE FROM facturas WHERE id_pedido = ?";
            $stmtFacturas = $this->db->prepare($sqlFacturas);
            $stmtFacturas->execute([$id]);

            // 2. Eliminar pagos asociados
            $sqlPagos = "DELETE FROM pagos WHERE id_pedido = ?";
            $stmtPagos = $this->db->prepare($sqlPagos);
            $stmtPagos->execute([$id]);

            // 3. Eliminar detalles del pedido
            $sqlDetalles = "DELETE FROM detalle_pedido WHERE id_pedido = ?";
            $stmtDetalles = $this->db->prepare($sqlDetalles);
            $stmtDetalles->execute([$id]);

            // 4. Finalmente eliminar el pedido
            $sql = "DELETE FROM pedidos WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Pedido::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de pedidos (para dashboard admin)
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'preparando' THEN 1 ELSE 0 END) as preparando,
                    SUM(CASE WHEN estado = 'listo' THEN 1 ELSE 0 END) as listos,
                    SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregados,
                    SUM(total) as ventas_totales
                    FROM pedidos
                    WHERE estado != 'cancelado'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Pedido::getEstadisticas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un pedido está bloqueado (entregado)
     */
    public function estaBloqueado($id) {
        try {
            $pedido = $this->findById($id);
            return $pedido && $pedido['estado'] === 'entregado';
        } catch(PDOException $e) {
            error_log("Error en Pedido::estaBloqueado(): " . $e->getMessage());
            return false;
        }
    }
}