<?php
session_start();
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../middleware/auth.php';

checkAdmin();

$productoModel = new Producto();
$action = $_GET['action'] ?? 'index';

// ruta de subida de imágenes (asegúrate de crear la carpeta)
$uploadDir = __DIR__ . '/../public/assets/img/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

switch ($action) {
    case 'create':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');

        $imagenNombre = null;
        if (!empty($_FILES['imagen']['name'])) {
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $imagenNombre = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $imagenNombre);
        }

        if ($productoModel->create($nombre, $descripcion, $precio, $imagenNombre, $categoria)) {
            // Crear registro de inventario automáticamente
            $id_producto = $productoModel->getLastInsertId();
            
            require_once __DIR__ . '/../models/Inventario.php';
            $inventarioModel = new Inventario();
            $inventarioModel->crearRegistroInicial($id_producto);
            
            $_SESSION['success'] = 'Producto e inventario creados correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear el producto';
        }
    }
    header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
    exit;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $categoria = trim($_POST['categoria'] ?? '');
            $disponible = isset($_POST['disponible']) ? 1 : 0;

            $imagenNombre = null;
            if (!empty($_FILES['imagen']['name'])) {
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $imagenNombre = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $imagenNombre);
                $old = $productoModel->findById($id);
                if ($old && !empty($old['imagen']) && file_exists($uploadDir . $old['imagen'])) {
                    @unlink($uploadDir . $old['imagen']);
                }
            }

            $productoModel->update($id, $nombre, $descripcion, $precio, $imagenNombre, $categoria, $disponible);
        }
        header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
        exit;

 case 'delete':
    $id = intval($_GET['id'] ?? 0);
    
    if (!$id) {
        $_SESSION['error'] = 'ID de producto inválido';
        header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
        exit;
    }
    
    try {
        // Obtener información del producto
        $item = $productoModel->findById($id);
        
        if (!$item) {
            $_SESSION['error'] = 'Producto no encontrado';
            header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
            exit;
        }
        
        // Eliminar inventario asociado primero
        require_once __DIR__ . '/../models/Inventario.php';
        $inventarioModel = new Inventario();
        $inventarioModel->delete($id);
        
        // Eliminar imagen si existe
        if (!empty($item['imagen']) && file_exists($uploadDir . $item['imagen'])) {
            @unlink($uploadDir . $item['imagen']);
        }
        
        // Eliminar producto
        if ($productoModel->delete($id)) {
            $_SESSION['success'] = 'Producto eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el producto';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
    exit;

    default:
        header('Location: /Proyecto_aula/proyecto/views/admin/productos/index.php');
        exit;
}