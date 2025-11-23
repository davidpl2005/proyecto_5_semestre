<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Factura.php';
require_once __DIR__ . '/../models/DetallePedido.php';

checkAuth();

$facturaModel = new Factura();
$detalleModel = new DetallePedido();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'ver':
        // Ver detalle de una factura
        $id = intval($_GET['id'] ?? 0);
        $factura = $facturaModel->findById($id);

        if (!$factura) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
            exit;
        }

        // Verificar permisos
        if ($factura['id_usuario'] != $_SESSION['user']['id'] && $_SESSION['user']['rol'] != 'admin') {
            $_SESSION['error'] = 'No tienes permiso para ver esta factura';
            header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
            exit;
        }

        // Obtener detalles del pedido
        $detalles = $detalleModel->getByPedido($factura['id_pedido']);

        $_SESSION['factura_actual'] = $factura;
        $_SESSION['detalles_factura'] = $detalles;
        header('Location: /Proyecto_aula/proyecto/views/facturas/detalle.php');
        exit;

    case 'lista':
        // Ver lista de facturas del usuario
        header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
        exit;

    case 'admin_lista':
        // Ver todas las facturas (solo admin)
        checkAdmin();
        header('Location: /Proyecto_aula/proyecto/views/admin/facturas/index.php');
        exit;

    case 'descargar':
        // Descargar factura como HTML imprimible
        $id = intval($_GET['id'] ?? 0);
        $factura = $facturaModel->findById($id);

        if (!$factura) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
            exit;
        }

        // Verificar permisos
        if ($factura['id_usuario'] != $_SESSION['user']['id'] && $_SESSION['user']['rol'] != 'admin') {
            $_SESSION['error'] = 'No tienes permiso para descargar esta factura';
            header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
            exit;
        }

        // Obtener detalles del pedido
        $detalles = $detalleModel->getByPedido($factura['id_pedido']);

        $_SESSION['factura_actual'] = $factura;
        $_SESSION['detalles_factura'] = $detalles;
        header('Location: /Proyecto_aula/proyecto/views/facturas/imprimir.php');
        exit;

    case 'por_pedido':
        // Obtener factura de un pedido específico
        $id_pedido = intval($_GET['id_pedido'] ?? 0);
        $factura = $facturaModel->findByPedido($id_pedido);

        if (!$factura) {
            $_SESSION['error'] = 'No hay factura para este pedido';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Redirigir a ver la factura
        header('Location: /Proyecto_aula/proyecto/controllers/FacturaController.php?action=ver&id=' . $factura['id_factura']);
        exit;

    case 'por_pago':
        // Obtener factura de un pago específico
        $id_pago = intval($_GET['id_pago'] ?? 0);
        $factura = $facturaModel->findByPago($id_pago);

        if (!$factura) {
            $_SESSION['error'] = 'No hay factura para este pago';
            header('Location: /Proyecto_aula/proyecto/views/pagos/index.php');
            exit;
        }

        // Redirigir a ver la factura
        header('Location: /Proyecto_aula/proyecto/controllers/FacturaController.php?action=ver&id=' . $factura['id_factura']);
        exit;

    case 'eliminar':
        // Eliminar una factura (solo admin)
        checkAdmin();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($facturaModel->delete($id)) {
            $_SESSION['success'] = 'Factura eliminada correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la factura';
        }
        
        header('Location: /Proyecto_aula/proyecto/views/admin/facturas/index.php');
        exit;

    default:
        // Redirigir según el rol
        if ($_SESSION['user']['rol'] === 'admin') {
            header('Location: /Proyecto_aula/proyecto/views/admin/facturas/index.php');
        } else {
            header('Location: /Proyecto_aula/proyecto/views/facturas/index.php');
        }
        exit;
}