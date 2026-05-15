<?php
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Inventario.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $productoModel  = new Producto();
    $inventarioModel = new Inventario();

    $productos = $productoModel->getAll();
    $inventario = $inventarioModel->getAll();

    // Crear mapa de stock por producto
    $stock = [];
    foreach ($inventario as $inv) {
        $stock[$inv['id_producto']] = $inv['cantidad'];
    }

    // Agregar stock a cada producto
    $resultado = [];
    foreach ($productos as $p) {
        $p['stock'] = $stock[$p['id_producto']] ?? 0;
        $p['imagen_url'] = !empty($p['imagen'])
            ? 'http://10.0.2.2/Proyecto_aula/proyecto/public/assets/img/products/' . $p['imagen']
            : null;
        $resultado[] = $p;
    }

    responder($resultado);
}

responderError('Método no permitido', 405);