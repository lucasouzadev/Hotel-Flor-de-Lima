<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Buscar categorias e drinks
$categories = $db->fetchAll("SELECT * FROM drink_categories ORDER BY name");
$drinks = $db->fetchAll("SELECT d.*, dc.name as category_name FROM drinks d JOIN drink_categories dc ON d.category_id = dc.id WHERE d.is_available = true ORDER BY dc.name, d.name");

// Agrupar drinks por categoria
$drinksByCategory = [];
foreach ($drinks as $drink) {
    $drinksByCategory[$drink['category_name']][] = $drink;
}

// Processar pedido
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!$auth->isLoggedIn()) {
        $message = 'Você precisa fazer login para adicionar itens ao carrinho.';
        $messageType = 'error';
    } else {
        $drinkId = (int)$_POST['drink_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        
            $drink = $db->fetch("SELECT * FROM drinks WHERE id = ? AND is_available = true", [$drinkId]);
        
        if ($drink) {
            // Adicionar ao carrinho (simulado via JavaScript)
            $message = "{$drink['name']} adicionado ao carrinho!";
            $messageType = 'success';
        } else {
            $message = 'Drink não encontrado ou indisponível.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Celina - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bar.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <h2>Hotel Flor de Lima</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php">Início</a></li>
                    <li><a href="bar-celina.php" class="active">Bar Celina</a></li>
                    <li><a href="reservations.php">Reservas</a></li>
                    <li><a href="newspaper.php">O CORVO</a></li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Registro</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="bar-hero">
        <div class="bar-hero-content">
            <h1>Bar Celina</h1>
            <p>Uma experiência única onde tradições eslavas e japonesas se encontram</p>
            <div class="bar-highlights">
                <div class="highlight">
                    <i class="fas fa-cocktail"></i>
                    <span>Drinks Especiais</span>
                </div>
                <div class="highlight">
                    <i class="fas fa-leaf"></i>
                    <span>Opções Sem Álcool</span>
                </div>
                <div class="highlight">
                    <i class="fas fa-child"></i>
                    <span>Menu Infantil</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Bar Info Section -->
    <section class="bar-info">
        <div class="container">
            <div class="bar-info-content">
                <div class="bar-description">
                    <h2>Sobre o Bar Celina</h2>
                    <p>O Bar Celina é o coração gastronômico do Hotel Flor de Lima. Nossa carta de drinks é cuidadosamente elaborada, misturando técnicas tradicionais eslavas com a elegância e precisão da cultura japonesa.</p>
                    <p>Cada drink é uma obra de arte, preparado com ingredientes premium e apresentado de forma única. Nossa equipe de bartenders especializados está sempre pronta para criar experiências memoráveis para nossos hóspedes.</p>
                    
                    <div class="bar-features">
                        <div class="feature">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Horário de Funcionamento</h4>
                                <p>Segunda a Domingo: 18:00 - 02:00</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Localização</h4>
                                <p>Térreo do Hotel - Vista para o jardim</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Capacidade</h4>
                                <p>Até 50 pessoas confortavelmente</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bar-image">
                    <img src="assets/images/bar-celina-interior.jpg" alt="Interior do Bar Celina">
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section class="bar-menu">
        <div class="container">
            <h2>Carta de Drinks</h2>
            
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($drinksByCategory)): ?>
                <div class="no-drinks">
                    <i class="fas fa-glass-martini-alt"></i>
                    <h3>Menu em Atualização</h3>
                    <p>Estamos atualizando nossa carta de drinks. Volte em breve!</p>
                </div>
            <?php else: ?>
                <?php foreach ($drinksByCategory as $categoryName => $categoryDrinks): ?>
                    <div class="menu-category">
                        <h3 class="category-title">
                            <i class="fas fa-<?php echo getCategoryIcon($categoryName); ?>"></i>
                            <?php echo htmlspecialchars($categoryName); ?>
                        </h3>
                        
                        <div class="drinks-grid">
                            <?php foreach ($categoryDrinks as $drink): ?>
                                <div class="drink-card" data-drink-id="<?php echo $drink['id']; ?>">
                                    <div class="drink-image">
                                        <img src="assets/images/drinks/<?php echo $drink['image'] ?? 'default-drink.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($drink['name']); ?>"
                                             onerror="this.src='assets/images/drinks/default-drink.jpg'">
                                        <?php if ($drink['is_alcoholic']): ?>
                                            <div class="alcohol-badge">
                                                <i class="fas fa-wine-glass-alt"></i>
                                                <?php echo $drink['alcohol_content']; ?>%
                                            </div>
                                        <?php else: ?>
                                            <div class="non-alcohol-badge">
                                                <i class="fas fa-leaf"></i>
                                                Sem Álcool
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="drink-info">
                                        <h4 class="drink-name"><?php echo htmlspecialchars($drink['name']); ?></h4>
                                        <p class="drink-description"><?php echo htmlspecialchars($drink['description']); ?></p>
                                        
                                        <div class="drink-details">
                                            <div class="ingredients">
                                                <strong>Ingredientes:</strong>
                                                <span><?php echo htmlspecialchars($drink['ingredients']); ?></span>
                                            </div>
                                            
                                            <div class="preparation-info">
                                                <span class="prep-time">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo $drink['preparation_time']; ?> min
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="drink-footer">
                                            <div class="price">
                                                R$ <?php echo number_format($drink['price'], 2, ',', '.'); ?>
                                            </div>
                                            
                                            <div class="drink-actions">
                                                <div class="quantity-selector">
                                                    <button type="button" class="qty-btn minus" onclick="changeQuantity(<?php echo $drink['id']; ?>, -1)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" id="qty-<?php echo $drink['id']; ?>" value="1" min="1" max="10">
                                                    <button type="button" class="qty-btn plus" onclick="changeQuantity(<?php echo $drink['id']; ?>, 1)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                
                                                <button type="button" class="btn btn-primary add-to-cart" 
                                                        onclick="addToCart(<?php echo $drink['id']; ?>, <?php echo $drink['price']; ?>, '<?php echo htmlspecialchars($drink['name']); ?>')">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    Adicionar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recommendations Section -->
    <section class="recommendations">
        <div class="container">
            <h2>Recomendações do Chef</h2>
            <div class="recommendations-grid">
                <div class="recommendation">
                    <div class="recommendation-image">
                        <img src="assets/images/drinks/sakura-sour.jpg" alt="Sakura Sour">
                    </div>
                    <div class="recommendation-content">
                        <h3>Sakura Sour</h3>
                        <p>Nossa especialidade japonesa com flores de cerejeira e um toque de elegância oriental.</p>
                        <div class="recommendation-tags">
                            <span class="tag">Mais Pedido</span>
                            <span class="tag">Especial</span>
                        </div>
                    </div>
                </div>
                
                <div class="recommendation">
                    <div class="recommendation-image">
                        <img src="assets/images/drinks/slavic-mule.jpg" alt="Slavic Mule">
                    </div>
                    <div class="recommendation-content">
                        <h3>Slavic Mule</h3>
                        <p>Moscow Mule com ingredientes tradicionais eslavos e um twist especial do nosso bartender.</p>
                        <div class="recommendation-tags">
                            <span class="tag">Tradicional</span>
                            <span class="tag">Refrescante</span>
                        </div>
                    </div>
                </div>
                
                <div class="recommendation">
                    <div class="recommendation-image">
                        <img src="assets/images/drinks/matcha-martini.jpg" alt="Matcha Martini">
                    </div>
                    <div class="recommendation-content">
                        <h3>Matcha Martini</h3>
                        <p>Martini clássico reinventado com chá verde matcha e creme de coco para uma experiência única.</p>
                        <div class="recommendation-tags">
                            <span class="tag">Inovador</span>
                            <span class="tag">Premium</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Hotel Flor de Lima</h3>
                    <p>Uma experiência única de hospedagem e gastronomia, onde tradições eslavas e japonesas se encontram.</p>
                </div>
                <div class="footer-section">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="bar-celina.php">Bar Celina</a></li>
                        <li><a href="reservations.php">Reservas</a></li>
                        <li><a href="newspaper.php">O CORVO</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contato</h4>
                    <p>Rua das Flores, 123<br>Centro - Lima, PE</p>
                    <p>(81) 3456-7890</p>
                    <p>contato@hotelflordeLima.com.br</p>
                </div>
                <div class="footer-section">
                    <h4>Redes Sociais</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Hotel Flor de Lima. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Carrinho de Compras</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="cartItems"></div>
                <div id="cartEmpty" class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Seu carrinho está vazio</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="cart-total">
                    <strong>Total: R$ <span id="cartTotal">0,00</span></strong>
                </div>
                <div class="cart-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCart()">Continuar Comprando</button>
                    <button type="button" class="btn btn-primary" onclick="checkout()">Finalizar Pedido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/bar.js"></script>
</body>
</html>

<?php
function getCategoryIcon($categoryName) {
    $icons = [
        'Drinks Eslavos' => 'vodka-bottle',
        'Drinks Japoneses' => 'leaf',
        'Drinks Sem Álcool' => 'glass-water',
        'Soft Drinks & Shakes' => 'ice-cream',
        'Menu Infantil' => 'child'
    ];
    
    return $icons[$categoryName] ?? 'glass-martini-alt';
}
?>


