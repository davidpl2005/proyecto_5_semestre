<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Pedido.php';

checkAuth();

$pagoModel = new Pago();
$pedidoModel = new Pedido();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'mostrar_forma_pago':
        // Mostrar formulario de pago después de crear el pedido
        $id_pedido = $_GET['id_pedido'] ?? 0;

        // Verificar que el pedido existe y pertenece al usuario
        $pedido = $pedidoModel->findById($id_pedido);

        if (!$pedido) {
            $_SESSION['error'] = 'Pedido no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
            exit;
        }

        if ($pedido['id_usuario'] != $_SESSION['user']['id']) {
            $_SESSION['error'] = 'No tienes permiso para acceder a este pedido';
            header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
            exit;
        }

        // Verificar si ya tiene pago
        if ($pagoModel->pedidoTienePago($id_pedido)) {
            $_SESSION['error'] = 'Este pedido ya ha sido pagado';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Guardar datos del pedido en sesión para la vista
        $_SESSION['pedido_a_pagar'] = $pedido;
        header('Location: /Proyecto_aula/proyecto/views/pagos/form_pago.php');
        exit;

    case 'procesar_pago':
        // Procesar el pago
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
            exit;
        }

        $id_pedido = intval($_POST['id_pedido'] ?? 0);
        $metodo_pago = $_POST['metodo_pago'] ?? '';
        $monto = floatval($_POST['monto'] ?? 0);

        // Validaciones
        if ($id_pedido <= 0) {
            $_SESSION['error'] = 'Pedido inválido';
            header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
            exit;
        }

        $metodos_validos = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodo_pago, $metodos_validos)) {
            $_SESSION['error'] = 'Método de pago inválido';
            header('Location: /Proyecto_aula/proyecto/controllers/PagoController.php?action=mostrar_forma_pago&id_pedido=' . $id_pedido);
            exit;
        }

        // Verificar que el pedido existe y pertenece al usuario
        $pedido = $pedidoModel->findById($id_pedido);

        if (!$pedido || $pedido['id_usuario'] != $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Error al procesar el pago';
            header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
            exit;
        }

        // Verificar que el monto coincide
        if (abs($monto - $pedido['total']) > 0.01) {
            $_SESSION['error'] = 'El monto no coincide con el total del pedido';
            header('Location: /Proyecto_aula/proyecto/controllers/PagoController.php?action=mostrar_forma_pago&id_pedido=' . $id_pedido);
            exit;
        }

        // Verificar si ya tiene pago
        if ($pagoModel->pedidoTienePago($id_pedido)) {
            $_SESSION['error'] = 'Este pedido ya ha sido pagado';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Crear el pago

        $id_pago = $pagoModel->create($id_pedido, $metodo_pago, $monto, 'completado');

        if ($id_pago) {
            // Generar factura automáticamente
            require_once __DIR__ . '/../models/Factura.php';
            $facturaModel = new Factura();

            // Calcular subtotal e IVA
            $subtotal = $monto / 1.19;
            $iva = $monto - $subtotal;

            $id_factura = $facturaModel->create(
                $id_pedido,
                $id_pago,
                $_SESSION['user']['id'],
                $subtotal,
                $iva,
                $monto,
                'Factura generada automáticamente'
            );

            $_SESSION['success'] = 'Pago procesado exitosamente. Factura generada.';
            $_SESSION['id_factura_generada'] = $id_factura;
            header('Location: /Proyecto_aula/proyecto/views/pagos/confirmacion.php?id_pago=' . $id_pago);
        }
        exit;

    case 'ver':
        // Ver detalle de un pago
        $id_pago = intval($_GET['id'] ?? 0);
        $pago = $pagoModel->findById($id_pago);

        if (!$pago) {
            $_SESSION['error'] = 'Pago no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Verificar que el pago pertenece al usuario (o es admin)
        $pedido = $pedidoModel->findById($pago['id_pedido']);
        if ($pedido['id_usuario'] != $_SESSION['user']['id'] && $_SESSION['user']['rol'] != 'admin') {
            $_SESSION['error'] = 'No tienes permiso para ver este pago';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        $_SESSION['pago_detalle'] = $pago;
        header('Location: /Proyecto_aula/proyecto/views/pagos/detalle.php');
        exit;

    case 'lista':
        // Ver lista de pagos del usuario
        header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
        exit;

    case 'admin_lista':
        // Ver todos los pagos (solo admin)
        checkAdmin();
        header('Location: /Proyecto_aula/proyecto/views/admin/pagos/index.php');
        exit;

    case 'cancelar':
        // Cancelar un pago (solo si está pendiente)
        $id_pago = intval($_GET['id'] ?? 0);
        $pago = $pagoModel->findById($id_pago);

        if (!$pago) {
            $_SESSION['error'] = 'Pago no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
            exit;
        }

        // Verificar permisos
        $pedido = $pedidoModel->findById($pago['id_pedido']);
        if ($pedido['id_usuario'] != $_SESSION['user']['id'] && $_SESSION['user']['rol'] != 'admin') {
            $_SESSION['error'] = 'No tienes permiso';
            header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
            exit;
        }

        if ($pago['estado_pago'] != 'pendiente') {
            $_SESSION['error'] = 'Solo se pueden cancelar pagos pendientes';
            header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
            exit;
        }

        if ($pagoModel->updateEstado($id_pago, 'cancelado')) {
            $_SESSION['success'] = 'Pago cancelado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al cancelar el pago';
        }

        header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
        exit;

    default:
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
}
