<?php
session_start();
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Carrito.php';
require_once __DIR__ . '/../../models/Inventario.php';

$model = new Producto();
$productos = $model->getAll();
$inventarioModel = new Inventario();

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

// Obtener inventario para TODOS los productos (logueado o no)
$inventario_completo = $inventarioModel->getAll();
$stock_productos = [];
foreach ($inventario_completo as $inv) {
    $stock_productos[$inv['id_producto']] = $inv['cantidad'];
}

// Agrupar productos por categoría
$categorias = [];
$productos_por_categoria = [];

foreach ($productos as $producto) {
    $categoria = $producto['categoria'] ?? 'Sin categoría';
    
    if (!in_array($categoria, $categorias)) {
        $categorias[] = $categoria;
    }
    
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

// Obtener categoría seleccionada
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
    <link rel="stylesheet" href="/Proyecto_aula/proyecto/public/assets/css/menu-extended.css">
</head>

<body>
    <div class="menu-container">
        <header class="menu-header">
            <div>
                <h1>Restaurante Bambino</h1>
                <?php if ($isLoggedIn): ?>
                    <nav>
                        <a href="/Proyecto_aula/proyecto/views/pedidos/index.php" class="nav-link">
                            Mis Pedidos
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/pagos/index.php" class="nav-link">
                            Mis Pagos
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/facturas/index.php" class="nav-link">
                            Mis Facturas
                        </a>
                        <a href="/Proyecto_aula/proyecto/views/carrito/index.php" class="nav-link">
                            🛒
                            <?php if ($totalItems > 0): ?>
                                <span class="badge-contador"><?= $totalItems ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <a href="/Proyecto_aula/proyecto/views/admin/dashboard.php" class="nav-link" style="background-color: rgba(102, 126, 234, 0.3);">
                                Panel Admin
                            </a>
                        <?php elseif ($userRole === 'chef'): ?>
                            <a href="/Proyecto_aula/proyecto/views/chef/dashboard.php" class="nav-link" style="background-color: rgba(230, 126, 34, 0.3);">
                                Panel Chef
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

        <?php if (!$isLoggedIn): ?>
            <div class="login-prompt">
                <div class="login-prompt-text">
                     Para agregar productos al carrito y realizar pedidos, debes iniciar sesión o registrarte
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
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
                <h2>Explora Nuestro Menú por Categorías</h2>
                <div class="categorias-buttons">
                    <a href="?categoria=todas" 
                       class="btn-categoria <?= $categoria_seleccionada === 'todas' ? 'active' : '' ?>">
                        Todas
                        <span class="categoria-count"><?= count($productos) ?></span>
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <?php 
                        $count = count($productos_por_categoria[$cat]);
                        ?>
                        <a href="?categoria=<?= urlencode($cat) ?>" 
                           class="btn-categoria <?= $categoria_seleccionada === $cat ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat) ?>
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
                    <div style="font-size: 64px; margin-bottom: 20px;">📦</div>
                    <h2>No hay productos disponibles</h2>
                    <p>Vuelve pronto para ver nuestro menú</p>
                </div>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                    <?php
                    $productos_categoria = $productos_por_categoria[$cat];
                    ?>
                    <div class="categoria-section">
                        <h2>
                            <?= htmlspecialchars($cat) ?>
                            <span class="productos-count">(<?= count($productos_categoria) ?> productos)</span>
                        </h2>
                        <div class="productos-grid">
                            <?php foreach ($productos_categoria as $p): ?>
                                <?php 
                                // Obtener stock para TODOS (logueados y no logueados)
                                $stock_actual = isset($stock_productos[$p['id_producto']]) ? $stock_productos[$p['id_producto']] : 0;
                                $sin_stock = $stock_actual == 0;
                                ?>
                                <div class="producto-card <?= !$p['disponible'] || $sin_stock ? 'no-disponible' : '' ?>">
                                    <?php if ($sin_stock): ?>
                                        <!-- Overlay de AGOTADO para TODOS los usuarios -->
                                        <div class="sin-stock-overlay">
                                            <div class="sin-stock-text">Agotado</div>
                                        </div>
                                    <?php endif; ?>
                                    
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

                                        <?php if ($isLoggedIn): ?>
                                            <!-- Mostrar stock SOLO para usuarios logueados -->
                                            <?php if ($stock_actual > 10): ?>
                                                <div class="stock-info stock-ok">
                                                    <span class="stock-label">Disponibles:</span>
                                                    <span class="stock-cantidad"><?= $stock_actual ?></span>
                                                </div>
                                            <?php elseif ($stock_actual > 0): ?>
                                                <div class="stock-info stock-bajo">
                                                    <span class="stock-label">¡Últimas unidades!</span>
                                                    <span class="stock-cantidad"><?= $stock_actual ?></span>
                                                </div>
                                            <?php else: ?>
                                                <div class="stock-info sin-stock">
                                                    <span class="stock-label">Agotado</span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($p['disponible'] && !$sin_stock): ?>
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
                                                            max="<?= $stock_actual ?>"
                                                            class="input-cantidad">
                                                    </div>
                                                    <button type="submit" class="btn-agregar">Agregar al Carrito</button>
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
                                                        Inicia Sesión para Comprar
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn-agregar" disabled style="background-color: #95a5a6; cursor: not-allowed;">
                                                <?= $sin_stock ? 'Agotado' : 'No Disponible' ?>
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
                <div class="categoria-section">
                    <h2>
                        <?= htmlspecialchars($categoria_seleccionada) ?>
                        <span class="productos-count">(<?= count($productos_filtrados) ?> productos)</span>
                    </h2>
                    <div class="productos-grid">
                        <?php foreach ($productos_filtrados as $p): ?>
                            <?php 
                            // Obtener stock para TODOS (logueados y no logueados)
                            $stock_actual = isset($stock_productos[$p['id_producto']]) ? $stock_productos[$p['id_producto']] : 0;
                            $sin_stock = $stock_actual == 0;
                            ?>
                            <div class="producto-card <?= !$p['disponible'] || $sin_stock ? 'no-disponible' : '' ?>">
                                <?php if ($sin_stock): ?>
                                    <!-- Overlay de AGOTADO para TODOS los usuarios -->
                                    <div class="sin-stock-overlay">
                                        <div class="sin-stock-text">Agotado</div>
                                    </div>
                                <?php endif; ?>
                                
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

                                    <?php if ($isLoggedIn): ?>
                                        <!-- Mostrar stock SOLO para usuarios logueados -->
                                        <?php if ($stock_actual > 10): ?>
                                            <div class="stock-info stock-ok">
                                                <span class="stock-label">Disponibles:</span>
                                                <span class="stock-cantidad"><?= $stock_actual ?></span>
                                            </div>
                                        <?php elseif ($stock_actual > 0): ?>
                                            <div class="stock-info stock-bajo">
                                                <span class="stock-label">¡Últimas unidades!</span>
                                                <span class="stock-cantidad"><?= $stock_actual ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="stock-info sin-stock">
                                                <span class="stock-label">Agotado</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($p['disponible'] && !$sin_stock): ?>
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
                                                        max="<?= $stock_actual ?>"
                                                        class="input-cantidad">
                                                </div>
                                                <button type="submit" class="btn-agregar">Agregar al Carrito</button>
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
                                                    Inicia Sesión para Comprar
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn-agregar" disabled style="background-color: #95a5a6; cursor: not-allowed;">
                                            <?= $sin_stock ? 'Agotado' : 'No Disponible' ?>
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
                    Ver Carrito 🛒
                    <span class="carrito-cantidad"><?= $totalItems ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <h3 style="margin-bottom: 15px;">Restaurante Bambino</h3>
            <p>La mejor comida de la ciudad</p>
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> Restaurante Bambino. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
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