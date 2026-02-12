<?php
session_start();
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Carrito.php';

// NO requerir autenticación - permitir acceso público
$model = new Producto();
$productos = $model->getAll();

// Verificar si el usuario está logueado
$isLoggedIn = isset($_SESSION['user']);
$userName = $isLoggedIn ? $_SESSION['user']['nombre'] : null;
$userRole = $isLoggedIn ? $_SESSION['user']['rol'] : null;

// Calcular items del carrito (solo si está logueado)

$totalItems = 0;
if ($isLoggedIn) {
    $carritoModel = new Carrito();
    $totalItems = $carritoModel->contarItems($_SESSION['user']['id']);
}

// Agrupar productos por categoría
$categorias = [];
$productos_por_categoria = [];

foreach ($productos as $producto) {
    $categoria = $producto['categoria'] ?? 'Sin categoría';
    
    // Agregar categoría si no existe
    if (!in_array($categoria, $categorias)) {
        $categorias[] = $categoria;
    }
    
    // Agrupar productos por categoría
    if (!isset($productos_por_categoria[$categoria])) {
        $productos_por_categoria[$categoria] = [];
    }
    $productos_por_categoria[$categoria][] = $producto;
}

// Ordenar categorías alfabéticamente (Sin categoría al final)
usort($categorias, function($a, $b) {
    if ($a === 'Sin categoría') return 1;
    if ($b === 'Sin categoría') return -1;
    return strcmp($a, $b);
});

// Obtener categoría seleccionada (si hay filtro)
$categoria_seleccionada = $_GET['categoria'] ?? 'todas';

// Filtrar productos si hay una categoría seleccionada
if ($categoria_seleccionada !== 'todas') {
    $productos_filtrados = $productos_por_categoria[$categoria_seleccionada] ?? [];
} else {
    $productos_filtrados = $productos;
}

$slides = [
    [
        'image' => 'slide1.jpg',
        'title' => 'Hamburguesas Gourmet',
        'description' => 'Las mejores hamburguesas de la ciudad con ingredientes premium'
    ],
    [
        'image' => 'slide5.webp',
        'title' => 'Pizzas Artesanales',
        'description' => 'Pizzas hechas al horno de leña con masa tradicional'
    ],
    [
        'image' => 'slide3.webp',
        'title' => 'Bebidas Refrescantes',
        'description' => 'Diferentes tipos de bebidas para acompañar tu comida'
    ],
    [
        'image' => 'slide4.png',
        'title' => 'Patas',
        'description' => 'Patas artesanales con salsas caseras'
    ],
    [
        'image' => 'slide2.jpg',
        'title' => 'Canastas',
        'description' => 'La mejor entrada para compartir con amigos y familia'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Menú - Restaurante Bambino</title>
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/menu.css">
    <style>
        .menu-header nav {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .nav-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .badge-contador {
            background-color: #e74c3c;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .producto-card.no-disponible {
            opacity: 0.6;
            position: relative;
        }

        .producto-card.no-disponible::after {
            content: 'No Disponible';
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .sin-imagen {
            width: 100%;
            height: 200px;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            font-size: 48px;
        }

        .btn-login-required {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        .btn-login-required:hover {
            background-color: #7f8c8d;
        }

        .login-prompt {
            background-color: #fff3cd;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f39c12;
            text-align: center;
        }

        .login-prompt-text {
            color: #856404;
            font-weight: 600;
            font-size: 15px;
        }

        /* Estilos para el filtro de categorías */
        .categorias-filter {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .categorias-filter h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }

        .categorias-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-categoria {
            padding: 12px 24px;
            background-color: #f8f9fa;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-categoria:hover {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        }

        .btn-categoria.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .categoria-count {
            background-color: rgba(255,255,255,0.3);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-categoria.active .categoria-count {
            background-color: rgba(255,255,255,0.4);
        }

        /* Sección de categoría con título */
        .categoria-section {
            margin-bottom: 50px;
        }

        .categoria-section h2 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .categoria-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .productos-count {
            color: #7f8c8d;
            font-size: 16px;
            font-weight: 400;
            margin-left: auto;
        }

        /* Animación de aparición */
        .producto-card {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mensaje cuando no hay productos en categoría */
        .categoria-vacia {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
            background-color: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .categoria-vacia-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>

<body>
    <div class="menu-container">
        <header class="menu-header">
            <div>
                <h1>🍽️ Restaurante Bambino</h1>
                <?php if ($isLoggedIn): ?>
                    <nav>
                        <a href="/Proyecto_aula/proyecto/views/pedidos/index.php" class="nav-link">
                            📦 Mis Pedidos
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/pagos/index.php" class="nav-link">
                            💳 Mis Pagos
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/facturas/index.php" class="nav-link">
                            🧾 Mis Facturas
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/carrito/index.php" class="nav-link">
                            🛒 Carrito
                            <?php if ($totalItems > 0): ?>
                                <span class="badge-contador"><?= $totalItems ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Enlaces adicionales para admin y chef -->
                        <?php if ($userRole === 'admin'): ?>
                            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="nav-link" style="background-color: rgba(102, 126, 234, 0.3);">
                                👑 Panel Admin
                            </a>
                        <?php elseif ($userRole === 'chef'): ?>
                            <a href="/Proyecto_aula/proyecto/views/chef/dashboard.php" class="nav-link" style="background-color: rgba(230, 126, 34, 0.3);">
                                👨‍🍳 Panel Chef
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <span>Bienvenido, <?= htmlspecialchars($userName) ?></span>
                    <a href="/Proyecto_aula/proyecto/controllers/AuthController.php?action=logout" class="btn-logout">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/Proyecto_aula/proyecto/views/auth/login.php" class="btn-logout" style="background-color: #667eea;">Iniciar Sesión</a>
                    <a href="/Proyecto_aula/proyecto/views/auth/register.php" class="btn-logout" style="background-color: #27ae60;">Registrarse</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Alerta para usuarios no logueados -->
        <?php if (!$isLoggedIn): ?>
            <div class="login-prompt">
                <div class="login-prompt-text">
                     Para agregar productos al carrito y realizar pedidos, debes iniciar sesión o registrarte
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ⚠ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Carrusel de imágenes -->
        <div class="carousel-container">
            <div class="carousel-wrapper">
                <div class="carousel-slides" id="carouselSlides">
                    <?php foreach ($slides as $index => $slide): ?>
                        <div class="carousel-slide">
                            <img src="/Proyecto_aula/proyecto/public/assets/img/products/carousel/<?= $slide['image'] ?>"
                                alt="<?= $slide['title'] ?>"
                                onerror="this.src='/Proyecto_aula/proyecto/public/assets/img/products/placeholder.jpg'">
                            <div class="carousel-overlay">
                                <h3><?= $slide['title'] ?></h3>
                                <p><?= $slide['description'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="carousel-btn carousel-btn-prev" onclick="moveSlide(-1)">‹</button>
                <button class="carousel-btn carousel-btn-next" onclick="moveSlide(1)">›</button>

                <div class="carousel-indicators">
                    <?php foreach ($slides as $index => $slide): ?>
                        <button class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>"
                            onclick="goToSlide(<?= $index ?>)"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Filtro de Categorías -->
        <?php if (!empty($categorias)): ?>
            <div class="categorias-filter">
                <h2>🍴 Explora Nuestro Menú por Categorías</h2>
                <div class="categorias-buttons">
                    <a href="?categoria=todas" 
                       class="btn-categoria <?= $categoria_seleccionada === 'todas' ? 'active' : '' ?>">
                        🍽️ Todas
                        <span class="categoria-count"><?= count($productos) ?></span>
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <?php 
                        $count = count($productos_por_categoria[$cat]);
                        // Emojis por categoría
                        $emoji_map = [
                            'Hamburguesas' => '🍔',
                            'Pizzas' => '🍕',
                            'Bebidas' => '🥤',
                            'Postres' => '🍰',
                            'Ensaladas' => '🥗',
                            'Entradas' => '🍟',
                            'Platos Fuertes' => '🍖',
                            'Sopas' => '🍲',
                            'Pastas' => '🍝',
                            'Mariscos' => '🦐',
                            'Sin categoría' => '📦'
                        ];
                        $emoji = $emoji_map[$cat] ?? '🍽️';
                        ?>
                        <a href="?categoria=<?= urlencode($cat) ?>" 
                           class="btn-categoria <?= $categoria_seleccionada === $cat ? 'active' : '' ?>">
                            <?= $emoji ?> <?= htmlspecialchars($cat) ?>
                            <span class="categoria-count"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Productos agrupados por categoría (cuando se muestra "Todas") -->
        <?php if ($categoria_seleccionada === 'todas'): ?>
            <?php if (empty($productos)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #7f8c8d;">
                    <div style="font-size: 64px; margin-bottom: 20px;">🍽️</div>
                    <h2>No hay productos disponibles</h2>
                    <p>Vuelve pronto para ver nuestro menú</p>
                </div>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                    <?php
                    $productos_categoria = $productos_por_categoria[$cat];
                    $emoji_map = [
                        'Hamburguesas' => '🍔',
                        'Pizzas' => '🍕',
                        'Bebidas' => '🥤',
                        'Postres' => '🍰',
                        'Ensaladas' => '🥗',
                        'Entradas' => '🍟',
                        'Platos Fuertes' => '🍖',
                        'Sopas' => '🍲',
                        'Pastas' => '🍝',
                        'Mariscos' => '🦐',
                        'Sin categoría' => '📦'
                    ];
                    $emoji = $emoji_map[$cat] ?? '🍽️';
                    ?>
                    <div class="categoria-section">
                        <h2>
                            <span class="categoria-icon"><?= $emoji ?></span>
                            <?= htmlspecialchars($cat) ?>
                            <span class="productos-count">(<?= count($productos_categoria) ?> productos)</span>
                        </h2>
                        <div class="productos-grid">
                            <?php foreach ($productos_categoria as $p): ?>
                                <div class="producto-card <?= !$p['disponible'] ? 'no-disponible' : '' ?>">
                                    <?php if (!empty($p['imagen'])): ?>
                                        <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($p['imagen']) ?>"
                                             alt="<?= htmlspecialchars($p['nombre']) ?>"
                                             class="producto-imagen">
                                    <?php else: ?>
                                        <div class="sin-imagen">📦</div>
                                    <?php endif; ?>
                                    <div class="producto-info">
                                        <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                                        <p class="producto-descripcion"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                                        <p class="producto-precio">$<?= number_format($p['precio'], 2) ?></p>

                                        <?php if ($p['disponible']): ?>
                                            <?php if ($isLoggedIn): ?>
                                                <form method="post" action="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=add"
                                                    class="form-agregar">
                                                    <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                                    <div class="cantidad-wrapper">
                                                        <label for="cantidad-<?= $p['id_producto'] ?>">Cantidad:</label>
                                                        <input type="number"
                                                            id="cantidad-<?= $p['id_producto'] ?>"
                                                            name="cantidad"
                                                            value="1"
                                                            min="1"
                                                            max="99"
                                                            class="input-cantidad">
                                                    </div>
                                                    <button type="submit" class="btn-agregar">🛒 Agregar al Carrito</button>
                                                </form>
                                            <?php else: ?>
                                                <div class="form-agregar">
                                                    <div class="cantidad-wrapper">
                                                        <label>Cantidad:</label>
                                                        <input type="number" value="1" min="1" disabled class="input-cantidad">
                                                    </div>
                                                    <button type="button" 
                                                            class="btn-agregar btn-login-required" 
                                                            onclick="mostrarAlertaLogin()"
                                                            title="Debes iniciar sesión para agregar al carrito">
                                                        🔒 Inicia Sesión para Comprar
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn-agregar" disabled style="background-color: #95a5a6; cursor: not-allowed;">
                                                No Disponible
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <!-- Mostrar solo productos de la categoría seleccionada -->
            <?php if (empty($productos_filtrados)): ?>
                <div class="categoria-vacia">
                    <div class="categoria-vacia-icon">📦</div>
                    <h2>No hay productos en esta categoría</h2>
                    <p>Intenta con otra categoría o mira todos los productos</p>
                    <br>
                    <a href="?categoria=todas" class="btn-agregar" style="display: inline-block; margin-top: 20px;">
                        Ver Todos los Productos
                    </a>
                </div>
            <?php else: ?>
                <?php
                $emoji_map = [
                    'Hamburguesas' => '🍔',
                    'Pizzas' => '🍕',
                    'Bebidas' => '🥤',
                    'Postres' => '🍰',
                    'Ensaladas' => '🥗',
                    'Entradas' => '🍟',
                    'Platos Fuertes' => '🍖',
                    'Sopas' => '🍲',
                    'Pastas' => '🍝',
                    'Mariscos' => '🦐',
                    'Sin categoría' => '📦'
                ];
                $emoji = $emoji_map[$categoria_seleccionada] ?? '🍽️';
                ?>
                <div class="categoria-section">
                    <h2>
                        <span class="categoria-icon"><?= $emoji ?></span>
                        <?= htmlspecialchars($categoria_seleccionada) ?>
                        <span class="productos-count">(<?= count($productos_filtrados) ?> productos)</span>
                    </h2>
                    <div class="productos-grid">
                        <?php foreach ($productos_filtrados as $p): ?>
                            <div class="producto-card <?= !$p['disponible'] ? 'no-disponible' : '' ?>">
                                <?php if (!empty($p['imagen'])): ?>
                                    <img src="/Proyecto_aula/proyecto/public/assets/img/products/<?= htmlspecialchars($p['imagen']) ?>"
                                         alt="<?= htmlspecialchars($p['nombre']) ?>"
                                         class="producto-imagen">
                                <?php else: ?>
                                    <div class="sin-imagen">📦</div>
                                <?php endif; ?>
                                <div class="producto-info">
                                    <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                                    <p class="producto-descripcion"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                                    <p class="producto-precio">$<?= number_format($p['precio'], 2) ?></p>

                                    <?php if ($p['disponible']): ?>
                                        <?php if ($isLoggedIn): ?>
                                            <form method="post" action="/Proyecto_aula/proyecto/controllers/CarritoController.php?action=add"
                                                class="form-agregar">
                                                <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                                <div class="cantidad-wrapper">
                                                    <label for="cantidad-<?= $p['id_producto'] ?>">Cantidad:</label>
                                                    <input type="number"
                                                        id="cantidad-<?= $p['id_producto'] ?>"
                                                        name="cantidad"
                                                        value="1"
                                                        min="1"
                                                        max="99"
                                                        class="input-cantidad">
                                                </div>
                                                <button type="submit" class="btn-agregar">🛒 Agregar al Carrito</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="form-agregar">
                                                <div class="cantidad-wrapper">
                                                    <label>Cantidad:</label>
                                                    <input type="number" value="1" min="1" disabled class="input-cantidad">
                                                </div>
                                                <button type="button" 
                                                        class="btn-agregar btn-login-required" 
                                                        onclick="mostrarAlertaLogin()"
                                                        title="Debes iniciar sesión para agregar al carrito">
                                                    🔒 Inicia Sesión para Comprar
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn-agregar" disabled style="background-color: #95a5a6; cursor: not-allowed;">
                                            No Disponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($isLoggedIn && $totalItems > 0): ?>
            <div class="carrito-preview">
                <a href="/Proyecto_aula/proyecto/views/carrito/index.php" class="btn-carrito">
                    🛒 Ver Carrito
                    <span class="carrito-cantidad"><?= $totalItems ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <h3 style="margin-bottom: 15px;">🍽️ Restaurante Bambino</h3>
            <p>La mejor comida de la ciudad</p>
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> Restaurante Bambino. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Función para mostrar alerta cuando usuario no logueado intenta agregar al carrito
        function mostrarAlertaLogin() {
            alert('⚠️ Debes iniciar sesión para agregar productos al carrito.\n\n¿Deseas iniciar sesión ahora?');
            window.location.href = '/Proyecto_aula/proyecto/views/auth/login.php';
        }

        // Carrusel
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        const totalSlides = slides.length;

        function showSlide(index) {
            if (totalSlides === 0) return;
            if (index >= totalSlides) currentSlide = 0;
            else if (index < 0) currentSlide = totalSlides - 1;
            else currentSlide = index;

            const slidesContainer = document.getElementById('carouselSlides');
            slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;

            if (indicators.length) {
                indicators.forEach((indicator, i) => {
                    indicator.classList.toggle('active', i === currentSlide);
                });
            }
        }

        function moveSlide(direction) {
            currentSlide += direction;
            showSlide(currentSlide);
        }

        function goToSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
        }

        showSlide(0);

        setInterval(() => {
            if (totalSlides === 0) return;
            currentSlide++;
            showSlide(currentSlide);
        }, 3000);

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    </script>

</body>

</html>