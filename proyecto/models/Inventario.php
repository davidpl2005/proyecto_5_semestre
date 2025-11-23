<?php
require_once __DIR__ . '/../config/database.php';

class Inventario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear registro inicial de inventario para un producto nuevo
     */
    public function crearRegistroInicial($id_producto, $stock_minimo = 10, $stock_maximo = 100) {
        try {
            // Verificar si ya existe
            $existe = $this->findByProducto($id_producto);
            if ($existe) {
                return true; // Ya existe, no crear duplicado
            }

            $sql = "INSERT INTO inventario (id_producto, cantidad, stock_minimo, stock_maximo) 
                    VALUES (?, 0, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_producto, $stock_minimo, $stock_maximo]);
        } catch(PDOException $e) {
            error_log("Error en Inventario::crearRegistroInicial(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener inventario de un producto
     */
    public function findByProducto($id_producto) {
        try {
            $sql = "SELECT i.*, p.nombre as nombre_producto, p.imagen, p.categoria, p.disponible
                    FROM inventario i
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    WHERE i.id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_producto]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Inventario::findByProducto(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todo el inventario con información de productos
     */
    public function getAll() {
        try {
            $sql = "SELECT i.*, p.nombre as nombre_producto, p.imagen, p.categoria, p.disponible, p.precio
                    FROM inventario i
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    ORDER BY p.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Inventario::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos con stock bajo (menor o igual al stock mínimo)
     */
    public function getStockBajo() {
        try {
            $sql = "SELECT i.*, p.nombre as nombre_producto, p.imagen
                    FROM inventario i
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    WHERE i.cantidad <= i.stock_minimo
                    ORDER BY i.cantidad ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Inventario::getStockBajo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar cantidad de inventario (entrada o salida)
     */
    public function actualizarCantidad($id_producto, $cantidad) {
        try {
            $sql = "UPDATE inventario 
                    SET cantidad = cantidad + ?, ultima_actualizacion = CURRENT_TIMESTAMP 
                    WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cantidad, $id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Inventario::actualizarCantidad(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Establecer cantidad exacta de inventario
     */
    public function setCantidad($id_producto, $cantidad) {
        try {
            $sql = "UPDATE inventario 
                    SET cantidad = ?, ultima_actualizacion = CURRENT_TIMESTAMP 
                    WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cantidad, $id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Inventario::setCantidad(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar stock mínimo y máximo
     */
    public function updateStocks($id_producto, $stock_minimo, $stock_maximo) {
        try {
            $sql = "UPDATE inventario 
                    SET stock_minimo = ?, stock_maximo = ? 
                    WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$stock_minimo, $stock_maximo, $id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Inventario::updateStocks(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si hay suficiente stock
     */
    public function hayStock($id_producto, $cantidad_requerida) {
        try {
            $inventario = $this->findByProducto($id_producto);
            if (!$inventario) {
                return false;
            }
            return $inventario['cantidad'] >= $cantidad_requerida;
        } catch(PDOException $e) {
            error_log("Error en Inventario::hayStock(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de inventario
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_productos,
                    SUM(cantidad) as total_unidades,
                    COUNT(CASE WHEN cantidad <= stock_minimo THEN 1 END) as productos_bajo_stock,
                    COUNT(CASE WHEN cantidad = 0 THEN 1 END) as productos_sin_stock,
                    COUNT(CASE WHEN cantidad > stock_minimo THEN 1 END) as productos_stock_ok
                    FROM inventario";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Inventario::getEstadisticas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Descontar inventario por pedido (cuando se entrega)
     */
    public function descontarPorPedido($detalles_pedido) {
        try {
            $this->db->beginTransaction();

            foreach ($detalles_pedido as $detalle) {
                $this->actualizarCantidad($detalle['id_producto'], -$detalle['cantidad']);
            }

            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Inventario::descontarPorPedido(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar registro de inventario
     */
    public function delete($id_producto) {
        try {
            $sql = "DELETE FROM inventario WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Inventario::delete(): " . $e->getMessage());
            return false;
        }
    }
}