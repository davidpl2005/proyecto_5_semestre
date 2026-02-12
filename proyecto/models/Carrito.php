<?php
require_once __DIR__ . '/../config/database.php';

class Carrito {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Agregar un producto al carrito del usuario
     */
    public function agregar($id_usuario, $id_producto, $cantidad, $precio) {
        try {
            // Verificar si el producto ya está en el carrito
            $sql = "SELECT cantidad FROM carrito WHERE id_usuario = ? AND id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario, $id_producto]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // Si existe, actualizar la cantidad
                $nueva_cantidad = $existe['cantidad'] + $cantidad;
                $sql = "UPDATE carrito SET cantidad = ?, precio = ? WHERE id_usuario = ? AND id_producto = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$nueva_cantidad, $precio, $id_usuario, $id_producto]);
            } else {
                // Si no existe, insertar nuevo registro
                $sql = "INSERT INTO carrito (id_usuario, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$id_usuario, $id_producto, $cantidad, $precio]);
            }
        } catch(PDOException $e) {
            error_log("Error en Carrito::agregar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los items del carrito de un usuario
     */
    public function obtenerPorUsuario($id_usuario) {
        try {
            $sql = "SELECT c.*, p.nombre, p.imagen, p.descripcion, p.disponible,
                    (c.cantidad * c.precio) as subtotal
                    FROM carrito c
                    INNER JOIN productos p ON c.id_producto = p.id_producto
                    WHERE c.id_usuario = ?
                    ORDER BY c.fecha_agregado DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Carrito::obtenerPorUsuario(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar la cantidad de un producto en el carrito
     */
    public function actualizarCantidad($id_usuario, $id_producto, $cantidad) {
        try {
            $sql = "UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cantidad, $id_usuario, $id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Carrito::actualizarCantidad(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un producto del carrito
     */
    public function eliminar($id_usuario, $id_producto) {
        try {
            $sql = "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_usuario, $id_producto]);
        } catch(PDOException $e) {
            error_log("Error en Carrito::eliminar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vaciar todo el carrito de un usuario
     */
    public function vaciar($id_usuario) {
        try {
            $sql = "DELETE FROM carrito WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_usuario]);
        } catch(PDOException $e) {
            error_log("Error en Carrito::vaciar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Contar items en el carrito
     */
    public function contarItems($id_usuario) {
        try {
            $sql = "SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error en Carrito::contarItems(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sincronizar carrito de sesión con la base de datos al iniciar sesión
     */
    public function sincronizarDesdeSession($id_usuario, $carrito_session) {
        try {
            if (empty($carrito_session)) {
                return true;
            }

            $this->db->beginTransaction();

            foreach ($carrito_session as $item) {
                $this->agregar(
                    $id_usuario,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio']
                );
            }

            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Carrito::sincronizarDesdeSession(): " . $e->getMessage());
            return false;
        }
    }
}