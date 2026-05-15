<?php
// Permitir peticiones desde la app Ionic
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Responder preflight requests de Ionic
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function responder($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit();
}

function responderError($mensaje, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit();
}