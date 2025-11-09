<?php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = ? LIMIT 1");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en findByEmail: " . $e->getMessage());
            return false;
        }
    }

    public function create($nombre, $correo, $passwordHash, $rol = 'cliente') {
        try {
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$nombre, $correo, $passwordHash, $rol]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id_usuario, nombre, correo, rol FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en findById: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT id_usuario, nombre, correo, rol FROM usuarios ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $nombre, $correo, $rol = 'cliente', $password = null) {
        try {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, password = ?, rol = ? WHERE id_usuario = ?";
                $params = [$nombre, $correo, $hash, $rol, $id];
            } else {
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?";
                $params = [$nombre, $correo, $rol, $id];
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = ? AND rol != 'admin'");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }
}