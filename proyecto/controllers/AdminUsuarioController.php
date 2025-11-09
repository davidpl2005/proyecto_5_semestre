<?php

session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Usuario.php';

checkAdmin();

$usuarioModel = new Usuario();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $rol = $_POST['rol'] ?? 'cliente';

            if ($usuarioModel->update($id, $nombre, $correo, $rol, $password)) {
                $_SESSION['success'] = 'Usuario actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar usuario';
            }
        }
        header('Location: /Proyecto_aula/proyecto/views/admin/usuarios/index.php');
        break;

    case 'delete':
        $id = $_GET['id'] ?? 0;
        if ($usuarioModel->delete($id)) {
            $_SESSION['success'] = 'Usuario eliminado correctamente';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el usuario';
        }
        header('Location: /Proyecto_aula/proyecto/views/admin/usuarios/index.php');
        break;

    default:
        header('Location: /Proyecto_aula/proyecto/views/admin/usuarios/index.php');
}