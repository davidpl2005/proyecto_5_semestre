<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/MovimientoInventario.php';
require_once __DIR__ . '/../models/Producto.php';

checkAdmin();

$inventarioModel = new Inventario();
$movimientoModel = new MovimientoInventario();
$productoModel = new Producto();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'lista':
        // Ver lista de inventario
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    case 'registrar_entrada':
        // Mostrar formulario de entrada
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/entrada.php');
        exit;

    case 'procesar_entrada':
        // Procesar entrada de inventario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        $id_producto = intval($_POST['id_producto'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id_producto <= 0 || $cantidad <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/entrada.php');
            exit;
        }

        // Actualizar inventario
        if ($inventarioModel->actualizarCantidad($id_producto, $cantidad)) {
            // Registrar movimiento
            $movimientoModel->registrar(
                $id_producto,
                'entrada',
                $cantidad,
                $descripcion,
                $_SESSION['user']['id']
            );
            $_SESSION['success'] = 'Entrada registrada correctamente';
        } else {
            $_SESSION['error'] = 'Error al registrar la entrada';
        }

        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    case 'registrar_salida':
        // Mostrar formulario de salida
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/salida.php');
        exit;

    case 'procesar_salida':
        // Procesar salida de inventario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        $id_producto = intval($_POST['id_producto'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id_producto <= 0 || $cantidad <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/salida.php');
            exit;
        }

        // Verificar que hay suficiente stock
        if (!$inventarioModel->hayStock($id_producto, $cantidad)) {
            $_SESSION['error'] = 'No hay suficiente stock disponible';
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/salida.php');
            exit;
        }

        // Actualizar inventario (cantidad negativa para salida)
        if ($inventarioModel->actualizarCantidad($id_producto, -$cantidad)) {
            // Registrar movimiento
            $movimientoModel->registrar(
                $id_producto,
                'salida',
                $cantidad,
                $descripcion,
                $_SESSION['user']['id']
            );
            $_SESSION['success'] = 'Salida registrada correctamente';
        } else {
            $_SESSION['error'] = 'Error al registrar la salida';
        }

        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    case 'editar':
        // Mostrar formulario de edición de stocks
        $id_producto = intval($_GET['id'] ?? 0);
        $inventario = $inventarioModel->findByProducto($id_producto);

        if (!$inventario) {
            $_SESSION['error'] = 'Producto no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        $_SESSION['inventario_editar'] = $inventario;
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/editar.php');
        exit;

    case 'actualizar':
        // Actualizar stocks mínimo y máximo
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        $id_producto = intval($_POST['id_producto'] ?? 0);
        $stock_minimo = intval($_POST['stock_minimo'] ?? 10);
        $stock_maximo = intval($_POST['stock_maximo'] ?? 100);

        if ($stock_minimo >= $stock_maximo) {
            $_SESSION['error'] = 'El stock mínimo debe ser menor al stock máximo';
            header('Location: /Proyecto_aula/proyecto/controllers/InventarioController.php?action=editar&id=' . $id_producto);
            exit;
        }

        if ($inventarioModel->updateStocks($id_producto, $stock_minimo, $stock_maximo)) {
            $_SESSION['success'] = 'Stocks actualizados correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar los stocks';
        }

        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    case 'movimientos':
        // Ver historial de movimientos
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/movimientos.php');
        exit;

    case 'alertas':
        // Ver productos con stock bajo
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/alertas.php');
        exit;

    case 'ajustar':
        // Ajustar stock manualmente (establecer cantidad exacta)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        $id_producto = intval($_POST['id_producto'] ?? 0);
        $nueva_cantidad = intval($_POST['cantidad'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Ajuste manual');

        if ($id_producto <= 0 || $nueva_cantidad < 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
            exit;
        }

        // Obtener cantidad actual
        $inventario_actual = $inventarioModel->findByProducto($id_producto);
        $cantidad_anterior = $inventario_actual['cantidad'];
        $diferencia = $nueva_cantidad - $cantidad_anterior;

        // Establecer nueva cantidad
        if ($inventarioModel->setCantidad($id_producto, $nueva_cantidad)) {
            // Registrar movimiento
            $tipo = $diferencia >= 0 ? 'entrada' : 'salida';
            $movimientoModel->registrar(
                $id_producto,
                $tipo,
                abs($diferencia),
                $motivo . ' (De ' . $cantidad_anterior . ' a ' . $nueva_cantidad . ')',
                $_SESSION['user']['id']
            );
            $_SESSION['success'] = 'Stock ajustado correctamente';
        } else {
            $_SESSION['error'] = 'Error al ajustar el stock';
        }

        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    case 'inicializar_faltantes':
        // Crear registros de inventario para productos que no lo tengan
        $productos = $productoModel->getAll();
        $creados = 0;

        foreach ($productos as $producto) {
            $existe = $inventarioModel->findByProducto($producto['id_producto']);
            if (!$existe) {
                $inventarioModel->crearRegistroInicial($producto['id_producto']);
                $creados++;
            }
        }

        if ($creados > 0) {
            $_SESSION['success'] = "Se crearon $creados registros de inventario faltantes";
        } else {
            $_SESSION['success'] = 'Todos los productos ya tienen registro de inventario';
        }

        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;

    default:
        header('Location: /Proyecto_aula/proyecto/views/admin/inventario/index.php');
        exit;
}