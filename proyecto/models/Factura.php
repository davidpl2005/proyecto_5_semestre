<?php
require_once __DIR__ . '/../config/database.php';

class Factura {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Generar número de factura único
     * Formato: FACT-YYYYMMDD-NNNN
     */
    private function generarNumeroFactura() {
        try {
            $fecha = date('Ymd');
            
            // Obtener el último número de factura del día
            $sql = "SELECT numero_factura FROM facturas 
                    WHERE numero_factura LIKE ? 
                    ORDER BY id_factura DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['FACT-' . $fecha . '-%']);
            $ultima = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ultima) {
                // Extraer el número secuencial y incrementar
                $partes = explode('-', $ultima['numero_factura']);
                $secuencial = intval($partes[2]) + 1;
            } else {
                // Primera factura del día
                $secuencial = 1;
            }
            
            return 'FACT-' . $fecha . '-' . str_pad($secuencial, 4, '0', STR_PAD_LEFT);
        } catch(PDOException $e) {
            error_log("Error en generarNumeroFactura(): " . $e->getMessage());
            return 'FACT-' . date('Ymd') . '-' . rand(1000, 9999);
        }
    }

    /**
     * Crear una nueva factura
     * @param int $id_pedido ID del pedido
     * @param int $id_pago ID del pago
     * @param int $id_usuario ID del usuario
     * @param float $subtotal Subtotal sin IVA
     * @param float $iva IVA
     * @param float $total Total con IVA
     * @param string $notas Notas adicionales
     * @return int|false ID de la factura creada o false si falla
     */
    public function create($id_pedido, $id_pago, $id_usuario, $subtotal, $iva, $total, $notas = '') {
        try {
            $numero_factura = $this->generarNumeroFactura();
            
            $sql = "INSERT INTO facturas (numero_factura, id_pedido, id_pago, id_usuario, subtotal, iva, total, notas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$numero_factura, $id_pedido, $id_pago, $id_usuario, $subtotal, $iva, $total, $notas])) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error en Factura::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener una factura por su ID
     */
    public function findById($id) {
        try {
            $sql = "SELECT f.*, u.nombre as nombre_usuario, u.correo, 
                    ped.fecha_pedido, p.metodo_pago, p.referencia as referencia_pago
                    FROM facturas f
                    INNER JOIN usuarios u ON f.id_usuario = u.id_usuario
                    INNER JOIN pedidos ped ON f.id_pedido = ped.id_pedido
                    INNER JOIN pagos p ON f.id_pago = p.id_pago
                    WHERE f.id_factura = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::findById(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener factura por número de factura
     */
    public function findByNumero($numero_factura) {
        try {
            $sql = "SELECT f.*, u.nombre as nombre_usuario, u.correo
                    FROM facturas f
                    INNER JOIN usuarios u ON f.id_usuario = u.id_usuario
                    WHERE f.numero_factura = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$numero_factura]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::findByNumero(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener factura por ID de pedido
     */
    public function findByPedido($id_pedido) {
        try {
            $sql = "SELECT * FROM facturas WHERE id_pedido = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::findByPedido(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener factura por ID de pago
     */
    public function findByPago($id_pago) {
        try {
            $sql = "SELECT * FROM facturas WHERE id_pago = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pago]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::findByPago(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las facturas de un usuario
     */
    public function getByUsuario($id_usuario) {
        try {
            $sql = "SELECT f.*, p.metodo_pago, ped.fecha_pedido
                    FROM facturas f
                    INNER JOIN pagos p ON f.id_pago = p.id_pago
                    INNER JOIN pedidos ped ON f.id_pedido = ped.id_pedido
                    WHERE f.id_usuario = ?
                    ORDER BY f.fecha_emision DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::getByUsuario(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las facturas (para admin)
     */
    public function getAll() {
        try {
            $sql = "SELECT f.*, u.nombre as nombre_usuario, u.correo, 
                    p.metodo_pago, ped.fecha_pedido
                    FROM facturas f
                    INNER JOIN usuarios u ON f.id_usuario = u.id_usuario
                    INNER JOIN pagos p ON f.id_pago = p.id_pago
                    INNER JOIN pedidos ped ON f.id_pedido = ped.id_pedido
                    ORDER BY f.fecha_emision DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si existe factura para un pedido
     */
    public function existeFactura($id_pedido) {
        try {
            $sql = "SELECT COUNT(*) as total FROM facturas WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch(PDOException $e) {
            error_log("Error en Factura::existeFactura(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de facturas
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_facturas,
                    SUM(subtotal) as total_subtotal,
                    SUM(iva) as total_iva,
                    SUM(total) as total_facturado,
                    AVG(total) as promedio_factura
                    FROM facturas";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::getEstadisticas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener facturas por rango de fechas
     */
    public function getByFechas($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT f.*, u.nombre as nombre_usuario, u.correo
                    FROM facturas f
                    INNER JOIN usuarios u ON f.id_usuario = u.id_usuario
                    WHERE DATE(f.fecha_emision) BETWEEN ? AND ?
                    ORDER BY f.fecha_emision DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Factura::getByFechas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar notas de la factura
     */
    public function updateNotas($id, $notas) {
        try {
            $sql = "UPDATE facturas SET notas = ? WHERE id_factura = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notas, $id]);
        } catch(PDOException $e) {
            error_log("Error en Factura::updateNotas(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una factura
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM facturas WHERE id_factura = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error en Factura::delete(): " . $e->getMessage());
            return false;
        }
    }
}