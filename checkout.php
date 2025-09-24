<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Require login para checkout
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Buscar itens do carrinho
$cartItems = [];
if (isset($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
} else {
    // Buscar do localStorage via JavaScript ou criar array vazio
    $cartItems = [];
}

// Processar checkout
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'process_checkout') {
    $paymentMethod = $_POST['payment_method'];
    $billingInfo = [
        'name' => trim($_POST['billing_name']),
        'email' => trim($_POST['billing_email']),
        'phone' => trim($_POST['billing_phone']),
        'address' => trim($_POST['billing_address']),
        'city' => trim($_POST['billing_city']),
        'state' => trim($_POST['billing_state']),
        'zip' => trim($_POST['billing_zip'])
    ];
    
    $specialInstructions = trim($_POST['special_instructions'] ?? '');
    
    // Validar dados
    if (empty($billingInfo['name']) || empty($billingInfo['email']) || empty($billingInfo['phone'])) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'error';
    } else {
        try {
            // Processar pedidos do bar
            $barOrders = array_filter($cartItems, function($item) {
                return $item['type'] === 'drink';
            });
            
            if (!empty($barOrders)) {
                // Criar pedido do bar
                $orderId = $db->insert('bar_orders', [
                    'user_id' => $currentUser['id'],
                    'table_number' => null,
                    'status' => 'pending',
                    'total_amount' => calculateBarTotal($barOrders),
                    'notes' => $specialInstructions
                ]);
                
                // Adicionar itens do pedido
                foreach ($barOrders as $item) {
                    $db->insert('bar_order_items', [
                        'order_id' => $orderId,
                        'drink_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['price'] * $item['quantity'],
                        'special_instructions' => ''
                    ]);
                }
            }
            
            // Limpar carrinho
            unset($_SESSION['cart']);
            
            $message = "Pedido processado com sucesso! ID do pedido: #{$orderId}";
            $messageType = 'success';
            
            // Redirecionar após 3 segundos
            header("refresh:3;url=dashboard.php");
            
        } catch (Exception $e) {
            $message = 'Erro ao processar pedido: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

function calculateBarTotal($barOrders) {
    $total = 0;
    foreach ($barOrders as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
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
                    <li><a href="bar-celina.php">Bar Celina</a></li>
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
    <section class="checkout-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Finalizar Pedido</h1>
                <p>Confirme seus dados e finalize sua compra</p>
            </div>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="checkout-content">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="checkout-layout">
                <!-- Checkout Form -->
                <div class="checkout-form-container">
                    <div class="checkout-steps">
                        <div class="step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-title">Informações</span>
                        </div>
                        <div class="step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-title">Pagamento</span>
                        </div>
                        <div class="step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-title">Confirmação</span>
                        </div>
                    </div>
                    
                    <form method="POST" class="checkout-form" id="checkoutForm">
                        <input type="hidden" name="action" value="process_checkout">
                        
                        <!-- Step 1: Billing Information -->
                        <div class="form-step active" data-step="1">
                            <h2><i class="fas fa-user"></i> Informações de Cobrança</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_name">Nome Completo *</label>
                                    <input type="text" id="billing_name" name="billing_name" 
                                           value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_email">Email *</label>
                                    <input type="email" id="billing_email" name="billing_email" 
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_phone">Telefone *</label>
                                    <input type="tel" id="billing_phone" name="billing_phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_address">Endereço *</label>
                                    <input type="text" id="billing_address" name="billing_address" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_city">Cidade *</label>
                                    <input type="text" id="billing_city" name="billing_city" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_state">Estado *</label>
                                    <select id="billing_state" name="billing_state" required>
                                        <option value="">Selecione</option>
                                        <option value="PE">Pernambuco</option>
                                        <option value="BA">Bahia</option>
                                        <option value="CE">Ceará</option>
                                        <option value="PB">Paraíba</option>
                                        <option value="AL">Alagoas</option>
                                        <option value="SE">Sergipe</option>
                                        <option value="RN">Rio Grande do Norte</option>
                                        <option value="MA">Maranhão</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="billing_zip">CEP *</label>
                                    <input type="text" id="billing_zip" name="billing_zip" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Payment -->
                        <div class="form-step" data-step="2">
                            <h2><i class="fas fa-credit-card"></i> Método de Pagamento</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="payment_card" name="payment_method" value="card" required>
                                    <label for="payment_card" class="payment-label">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Cartão de Crédito/Débito</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="payment_pix" name="payment_method" value="pix" required>
                                    <label for="payment_pix" class="payment-label">
                                        <i class="fas fa-qrcode"></i>
                                        <span>PIX</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="payment_cash" name="payment_method" value="cash" required>
                                    <label for="payment_cash" class="payment-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Dinheiro (Pagamento no Local)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="special_instructions">Instruções Especiais</label>
                                <textarea id="special_instructions" name="special_instructions" rows="3" 
                                          placeholder="Alguma instrução especial para seu pedido? (opcional)"></textarea>
                            </div>
                        </div>
                        
                        <!-- Step 3: Confirmation -->
                        <div class="form-step" data-step="3">
                            <h2><i class="fas fa-check-circle"></i> Confirmação</h2>
                            
                            <div class="confirmation-content">
                                <div class="order-summary">
                                    <h3>Resumo do Pedido</h3>
                                    <div id="orderItems"></div>
                                    <div class="order-total">
                                        <strong>Total: R$ <span id="orderTotal">0,00</span></strong>
                                    </div>
                                </div>
                                
                                <div class="terms-agreement">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="terms_agreement" required>
                                        <span class="checkmark"></span>
                                        Aceito os <a href="terms.php" target="_blank">termos de uso</a> e <a href="privacy.php" target="_blank">política de privacidade</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-navigation">
                            <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                                <i class="fas fa-arrow-left"></i>
                                Anterior
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                                Próximo
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                                <i class="fas fa-check"></i>
                                Finalizar Pedido
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary-container">
                    <h2><i class="fas fa-shopping-cart"></i> Resumo do Pedido</h2>
                    
                    <div class="order-items" id="cartItems">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Carregando itens...
                        </div>
                    </div>
                    
                    <div class="order-summary-footer">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">R$ 0,00</span>
                        </div>
                        <div class="summary-row">
                            <span>Taxa de serviço:</span>
                            <span id="serviceFee">R$ 0,00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="total">R$ 0,00</span>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>Preparo estimado: 15-30 min</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-truck"></i>
                            <span>Entrega no local</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Pagamento seguro</span>
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

    <script src="assets/js/script.js"></script>
    <script src="assets/js/checkout.js"></script>
</body>
</html>
