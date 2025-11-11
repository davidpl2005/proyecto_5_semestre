<?php
require_once __DIR__ . '/../config/database.php';

class DetallePedido {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear un detalle de pedido (agregar producto al pedido)
     * @param int $id_pedido ID del pedido
     * @param int $id_producto ID del producto
     * @param int $cantidad Cantidad del producto
     * @param float $precio_unitario Precio del producto al momento de la compra
     * @param float $subtotal Subtotal (cantidad * precio_unitario)
     * @return bool True si se creó correctamente
     */
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

    /**
     * Obtener todos los detalles de un pedido con información de productos
     * @param int $id_pedido ID del pedido
     * @return array Detalles del pedido con información de productos
     */
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

    /**
     * Obtener un detalle específico
     * @param int $id ID del detalle
     * @return array|false Detalle o false si no existe
     */
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

    /**
     * Eliminar todos los detalles de un pedido
     * @param int $id_pedido ID del pedido
     * @return bool True si se eliminó correctamente
     */
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

    /**
     * Eliminar un detalle específico
     * @param int $id ID del detalle
     * @return bool True si se eliminó correctamente
     */
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

    /**
     * Calcular el total de un pedido sumando sus detalles
     * @param int $id_pedido ID del pedido
     * @return float Total del pedido
     */
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

    /**
     * Obtener productos más vendidos (para estadísticas)
     * @param int $limite Número de productos a retornar
     * @return array Productos más vendidos
     */
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

    /**
     * Crear múltiples detalles de pedido (útil para el carrito)
     * @param int $id_pedido ID del pedido
     * @param array $items Array de items del carrito
     * @return bool True si se crearon todos correctamente
     */
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