<?php
require_once __DIR__ . '/../config/database.php';

class DetallePedido {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($id_pedido, $id_producto, $cantidad, $precio_unitario, $subtotal) {
        try {
            $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio_unitario, $subtotal]);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::create(): " . $e->getMessage());
            return false;
        }
    }

    public function getByPedido($id_pedido) {
        try {
            $sql = "SELECT dp.*, p.nombre as nombre_producto, p.imagen, p.descripcion
                    FROM detalle_pedido dp
                    INNER JOIN productos p ON dp.id_producto = p.id_producto
                    WHERE dp.id_pedido = ?
                    ORDER BY dp.id_detalle";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::getByPedido(): " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $sql = "SELECT dp.*, p.nombre as nombre_producto 
                    FROM detalle_pedido dp
                    INNER JOIN productos p ON dp.id_producto = p.id_producto
                    WHERE dp.id_detalle = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::findById(): " . $e->getMessage());
            return false;
        }
    }

   
    public function deleteByPedido($id_pedido) {
        try {
            $sql = "DELETE FROM detalle_pedido WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_pedido]);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::deleteByPedido(): " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM detalle_pedido WHERE id_detalle = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::delete(): " . $e->getMessage());
            return false;
        }
    }

    public function calcularTotal($id_pedido) {
        try {
            $sql = "SELECT SUM(subtotal) as total FROM detalle_pedido WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::calcularTotal(): " . $e->getMessage());
            return 0;
        }
    }

   
    public function getProductosMasVendidos($limite = 5) {
        try {
            $sql = "SELECT p.nombre, p.imagen, SUM(dp.cantidad) as total_vendido, 
                    SUM(dp.subtotal) as ventas_total
                    FROM detalle_pedido dp
                    INNER JOIN productos p ON dp.id_producto = p.id_producto
                    GROUP BY dp.id_producto, p.nombre, p.imagen
                    ORDER BY total_vendido DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en DetallePedido::getProductosMasVendidos(): " . $e->getMessage());
            return [];
        }
    }

 
    public function createMultiple($id_pedido, $items) {
        try {
            $this->db->beginTransaction();
            
            foreach ($items as $item) {
                $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $id_pedido,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio'],
                    $item['subtotal']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("Error en DetallePedido::createMultiple(): " . $e->getMessage());
            return false;
        }
    }
}