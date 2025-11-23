<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/DetallePedido.php';

checkAuth();

$pedidoModel = new Pedido();
$detalleModel = new DetallePedido();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'checkout':
        // Procesar el pedido desde el carrito
        if (empty($_SESSION['carrito'])) {
            $_SESSION['error'] = 'El carrito está vacío';
            header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
            exit;
        }

        // Calcular total
        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['subtotal'];
        }
        
        // Agregar IVA (19%)
        $total = $total * 1.19;

        // Crear el pedido
        $id_usuario = $_SESSION['user']['id'];
        $id_pedido = $pedidoModel->create($id_usuario, $total, 'pendiente');

        if ($id_pedido) {
            // Crear los detalles del pedido
            if ($detalleModel->createMultiple($id_pedido, $_SESSION['carrito'])) {
                // Limpiar el carrito
                $_SESSION['carrito'] = [];
                // Redirigir al formulario de pago
                header('Location: /Proyecto_aula/proyecto/controllers/PagoController.php?action=mostrar_forma_pago&id_pedido=' . $id_pedido);
            } else {
                $_SESSION['error'] = 'Error al procesar los detalles del pedido';
                header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
            }
        } else {
            $_SESSION['error'] = 'Error al crear el pedido';
            header('Location: /Proyecto_aula/proyecto/views/carrito/index.php');
        }
        exit;

    case 'view':
        // Ver detalles de un pedido
        $id = intval($_GET['id'] ?? 0);
        $pedido = $pedidoModel->findById($id);

        if (!$pedido) {
            $_SESSION['error'] = 'Pedido no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Verificar que el pedido pertenece al usuario (o es admin o chef)
        if ($pedido['id_usuario'] != $_SESSION['user']['id'] && 
            $_SESSION['user']['rol'] != 'admin' && 
            $_SESSION['user']['rol'] != 'chef') {
            $_SESSION['error'] = 'No tienes permiso para ver este pedido';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Obtener los detalles del pedido
        $detalles = $detalleModel->getByPedido($id);
        
        // Redirigir a la vista de detalle
        $_SESSION['pedido_actual'] = $pedido;
        $_SESSION['detalles_pedido'] = $detalles;
        header('Location: /Proyecto_aula/proyecto/views/pedidos/detalle.php');
        exit;

    case 'list':
        // Listar pedidos del usuario
        header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
        exit;

    case 'updateEstado':
        // Actualizar estado del pedido
        checkAdminOrChef();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $estado = $_POST['estado'] ?? '';

            $estados_validos = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
            
            if (!in_array($estado, $estados_validos)) {
                $_SESSION['error'] = 'Estado inválido';
                
                // Redirigir según el rol
                if ($_SESSION['user']['rol'] === 'chef') {
                    header('Location: /Proyecto_aula/proyecto/views/chef/dashboard.php');
                } else {
                    header('Location: /Proyecto_aula/proyecto/views/admin/pedidos/index.php');
                }
                exit;
            }

            // Obtener estado anterior
            $pedido = $pedidoModel->findById($id);
            $estado_anterior = $pedido['estado'];

            if ($pedidoModel->updateEstado($id, $estado)) {
                // Si el pedido pasa a "entregado", descontar del inventario
                if ($estado == 'entregado' && $estado_anterior != 'entregado') {
                    require_once __DIR__ . '/../models/Inventario.php';
                    require_once __DIR__ . '/../models/MovimientoInventario.php';
                    
                    $inventarioModel = new Inventario();
                    $movimientoModel = new MovimientoInventario();
                    $detalles = $detalleModel->getByPedido($id);
                    
                    // Descontar del inventario
                    if ($inventarioModel->descontarPorPedido($detalles)) {
                        // Registrar movimientos
                        foreach ($detalles as $detalle) {
                            $movimientoModel->registrar(
                                $detalle['id_producto'],
                                'salida',
                                $detalle['cantidad'],
                                'Venta - Pedido #' . $id,
                                $_SESSION['user']['id']
                            );
                        }
                    }
                }
                
                $_SESSION['success'] = 'Estado del pedido actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el estado';
            }
        }
        
        // Redirigir según el rol
        if ($_SESSION['user']['rol'] === 'chef') {
            header('Location: /Proyecto_aula/proyecto/views/chef/dashboard.php');
        } else {
            header('Location: /Proyecto_aula/proyecto/views/admin/pedidos/index.php');
        }
        exit;

    case 'cancel':
        // Cancelar un pedido (solo si está pendiente)
        $id = intval($_GET['id'] ?? 0);
        $pedido = $pedidoModel->findById($id);

        if (!$pedido) {
            $_SESSION['error'] = 'Pedido no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Verificar que el pedido pertenece al usuario
        if ($pedido['id_usuario'] != $_SESSION['user']['id']) {
            $_SESSION['error'] = 'No tienes permiso para cancelar este pedido';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        // Solo se puede cancelar si está pendiente
        if ($pedido['estado'] != 'pendiente') {
            $_SESSION['error'] = 'Solo puedes cancelar pedidos pendientes';
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
            exit;
        }

        if ($pedidoModel->updateEstado($id, 'cancelado')) {
            $_SESSION['success'] = 'Pedido cancelado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al cancelar el pedido';
        }

        header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
        exit;

    case 'delete':
        // Eliminar un pedido (solo admin)
        checkAdmin();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($pedidoModel->delete($id)) {
            $_SESSION['success'] = 'Pedido eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el pedido';
        }
        
        header('Location: /Proyecto_aula/proyecto/views/admin/pedidos/index.php');
        exit;

    default:
        // Redirigir según el rol
        if ($_SESSION['user']['rol'] === 'admin') {
            header('Location: /Proyecto_aula/proyecto/views/admin/pedidos/index.php');
        } elseif ($_SESSION['user']['rol'] === 'chef') {
            header('Location: /Proyecto_aula/proyecto/views/chef/dashboard.php');
        } else {
            header('Location: /Proyecto_aula/proyecto/views/pedidos/index.php');
        }
        exit;
}