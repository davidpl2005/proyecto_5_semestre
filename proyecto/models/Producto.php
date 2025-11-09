<?php

require_once __DIR__ . '/../config/database.php';

class Producto {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM productos");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Producto::getAll(): " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE id_producto = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Producto::findById(): " . $e->getMessage());
            return false;
        }
    }

    public function create($nombre, $descripcion, $precio, $imagen = null, $categoria = null) {
        try {
            $sql = "INSERT INTO productos (nombre, descripcion, precio, imagen, categoria) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nombre, $descripcion, $precio, $imagen, $categoria]);
        } catch(PDOException $e) {
            error_log("Error en Producto::create(): " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $nombre, $descripcion, $precio, $imagen = null, $categoria = null, $disponible = 1) {
        try {
            if ($imagen !== null) {
                $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria = ?, disponible = ? WHERE id_producto = ?";
                $params = [$nombre, $descripcion, $precio, $imagen, $categoria, $disponible, $id];
            } else {
                $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, disponible = ? WHERE id_producto = ?";
                $params = [$nombre, $descripcion, $precio, $categoria, $disponible, $id];
            }
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log("Error en Producto::update(): " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM productos WHERE id_producto = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error en Producto::delete(): " . $e->getMessage());
            return false;
        }
    }
}