<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../models/Usuario.php';

use Firebase\JWT\JWT;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Leer el body JSON que envía Ionic
$body = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST' && $action === 'login') {

    $correo   = trim($body['correo'] ?? '');
    $password = $body['password'] ?? '';

    if (empty($correo) || empty($password)) {
        responderError('Correo y contraseña son obligatorios');
    }

    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->findByEmail($correo);

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        responderError('Credenciales inválidas', 401);
    }

    // Crear el payload del token
    $payload = [
        'iat'  => time(),
        'exp'  => time() + JWT_EXPIRATION,
        'data' => [
            'id'     => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'rol'    => $usuario['rol']
        ]
    ];

    $token = JWT::encode($payload, JWT_SECRET, 'HS256');

    responder([
        'token'  => $token,
        'usuario' => [
            'id'     => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'rol'    => $usuario['rol']
        ]
    ]);
}

if ($method === 'POST' && $action === 'register') {

    $nombre   = trim($body['nombre'] ?? '');
    $correo   = trim($body['correo'] ?? '');
    $password = $body['password'] ?? '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        responderError('Todos los campos son obligatorios');
    }

    $usuarioModel = new Usuario();

    if ($usuarioModel->findByEmail($correo)) {
        responderError('El correo ya está registrado');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($usuarioModel->create($nombre, $correo, $hash)) {
        responder(['mensaje' => 'Usuario registrado correctamente'], 201);
    } else {
        responderError('Error al registrar el usuario', 500);
    }
}

responderError('Acción no válida');