<?php
session_start();
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Carrito.php';

class AuthController
{
    private $usuarioModel;
    private $carritoModel;
    private $baseUrl;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->carritoModel = new Carrito();
        $this->baseUrl = '/Proyecto_aula/proyecto';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            $_SESSION['error'] = 'Por favor ingrese correo y contraseña';
            header("Location: {$this->baseUrl}/views/auth/login.php");
            exit;
        }

        $usuario = $this->usuarioModel->findByEmail($correo);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $_SESSION['error'] = 'Credenciales inválidas';
            header("Location: {$this->baseUrl}/views/auth/login.php");
            exit;
        }

        // Guardar carrito de sesión temporal (si existe)
        $carrito_temporal = $_SESSION['carrito'] ?? [];

        // Establecer sesión del usuario
        $_SESSION['user'] = [
            'id' => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'rol' => $usuario['rol']
        ];

        // Sincronizar carrito temporal con la base de datos
        if (!empty($carrito_temporal)) {
            $this->carritoModel->sincronizarDesdeSession($usuario['id_usuario'], $carrito_temporal);
        }

        // Limpiar carrito de sesión
        unset($_SESSION['carrito']);

        if ($usuario['rol'] === 'admin') {
            header("Location: {$this->baseUrl}/views/admin/dashboard.php");
        } elseif ($usuario['rol'] === 'chef') {
            header("Location: {$this->baseUrl}/views/chef/dashboard.php");
        } else {
            header("Location: {$this->baseUrl}/views/menu/index.php");
        }
        exit;
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (empty($nombre) || empty($correo) || empty($password) || empty($password2)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios';
            header("Location: {$this->baseUrl}/views/auth/register.php");
            exit;
        }

        if ($password !== $password2) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header("Location: {$this->baseUrl}/views/auth/register.php");
            exit;
        }

        if ($this->usuarioModel->findByEmail($correo)) {
            $_SESSION['error'] = 'El correo ya está registrado';
            header("Location: {$this->baseUrl}/views/auth/register.php");
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($this->usuarioModel->create($nombre, $correo, $passwordHash)) {
            $_SESSION['success'] = 'Registro exitoso. Por favor inicia sesión';
            header("Location: {$this->baseUrl}/views/auth/login.php");
        } else {
            $_SESSION['error'] = 'Error al registrar el usuario';
            header("Location: {$this->baseUrl}/views/auth/register.php");
        }
        exit;
    }

    public function logout()
    {
        // NO limpiar el carrito al cerrar sesión
        // El carrito permanece en la base de datos
        session_destroy();
        header("Location: {$this->baseUrl}/views/menu/index.php");
        exit;
    }
}

// Manejo de rutas
$action = $_GET['action'] ?? '';
$controller = new AuthController();

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        header("Location: /Proyecto_aula/proyecto/views/auth/login.php");
        exit;
}
