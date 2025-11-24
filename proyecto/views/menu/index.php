<?php
session_start();
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/Producto.php';
checkAuth();

$model = new Producto();
$productos = $model->getAll();


$totalItems = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $totalItems += $item['cantidad'];
    }
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
        'image' => 'slide3.jpg',
        'title' => 'Patacones',
        'description' => 'Patacones con todo hecho con ingredientes frescos'
    ],
    [
        'image' => 'slide4.jpg',
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
    <title>Menú - Restaurante</title>
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
    </style>
</head>

<body>
    <div class="menu-container">
        <header class="menu-header">
            <div>
                <h1>🍽️ Nuestro Menú</h1>
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
                </nav>
            </div>
            <div class="user-info">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre']) ?></span>
                <a href="/Proyecto_aula/proyecto/controllers/AuthController.php?action=logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

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

        <div class="productos-grid">
            <?php if (empty($productos)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #7f8c8d;">
                    <div style="font-size: 64px; margin-bottom: 20px;">🍽️</div>
                    <h2>No hay productos disponibles</h2>
                    <p>Vuelve pronto para ver nuestro menú</p>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $p): ?>
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
                                <button class="btn-agregar" disabled style="background-color: #95a5a6; cursor: not-allowed;">
                                    No Disponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalItems > 0): ?>
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