<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function obtenerUsuarioDelToken() {
    $headers = getallheaders();
    
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authorization)) {
        responderError('Token no proporcionado', 401);
    }

    // El token viene como "Bearer eyJ..."
    $partes = explode(' ', $authorization);

    if (count($partes) !== 2 || $partes[0] !== 'Bearer') {
        responderError('Formato de token inválido', 401);
    }

    $token = $partes[1];

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        responderError('Token inválido o expirado', 401);
    }
}

function verificarAdmin() {
    $usuario = obtenerUsuarioDelToken();
    if ($usuario['rol'] !== 'admin') {
        responderError('Acceso denegado', 403);
    }
    return $usuario;
}