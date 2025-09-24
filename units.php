<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Page configuration
$pageTitle = 'Unidades e Quartos';
$pageDescription = 'Conheça nossos quartos e suítes exclusivos, cada um com sua personalidade única';
$additionalCSS = ['assets/css/units.css'];
$additionalJS = ['assets/js/units.js'];

// Buscar tipos de quartos e quartos disponíveis
$roomTypes = $db->fetchAll("SELECT * FROM room_types ORDER BY base_price");
$availableRooms = $db->fetchAll("SELECT r.*, rt.name as type_name, rt.description, rt.base_price, rt.max_occupancy, rt.amenities, rt.images 
                                 FROM rooms r 
                                 JOIN room_types rt ON r.room_type_id = rt.id 
                                 WHERE r.status = 'available' 
                                 ORDER BY rt.base_price, r.room_number");

// Processar busca de disponibilidade
$searchResults = [];
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'search_availability') {
    $checkIn = $_POST['check_in'] ?? '';
    $checkOut = $_POST['check_out'] ?? '';
    $guests = (int)($_POST['guests'] ?? 1);
    $roomType = $_POST['room_type'] ?? '';
    
    if (empty($checkIn) || empty($checkOut) || $guests < 1) {
        $message = 'Por favor, preencha todos os campos de busca.';
        $messageType = 'error';
    } else {
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        
        if ($checkInDate >= $checkOutDate) {
            $message = 'A data de check-out deve ser posterior à data de check-in.';
            $messageType = 'error';
        } elseif ($checkInDate < new DateTime()) {
            $message = 'A data de check-in não pode ser no passado.';
            $messageType = 'error';
        } else {
            // Buscar quartos disponíveis
            $whereConditions = ["r.status = 'available'"];
            $params = [];
            
            if (!empty($roomType)) {
                $whereConditions[] = "rt.id = ?";
                $params[] = $roomType;
            }
            
            $whereConditions[] = "rt.max_occupancy >= ?";
            $params[] = $guests;
            
            // Verificar conflitos de reserva
            $whereConditions[] = "r.id NOT IN (
                SELECT DISTINCT room_id 
                FROM reservations 
                WHERE status IN ('confirmed', 'pending')
                AND (
                    (check_in <= ? AND check_out > ?) OR
                    (check_in < ? AND check_out >= ?) OR
                    (check_in >= ? AND check_out <= ?)
                )
            )";
            $params = array_merge($params, [$checkOut, $checkIn, $checkOut, $checkIn, $checkIn, $checkOut]);
            
            $searchResults = $db->fetchAll(
                "SELECT r.*, rt.name as type_name, rt.description, rt.base_price, rt.max_occupancy, rt.amenities, rt.images,
                        (? - ?) as nights
                 FROM rooms r 
                 JOIN room_types rt ON r.room_type_id = rt.id 
                 WHERE " . implode(' AND ', $whereConditions) . "
                 ORDER BY rt.base_price, r.room_number",
                array_merge([$checkOut, $checkIn], $params)
            );
            
            if (empty($searchResults)) {
                $message = 'Nenhum quarto disponível para as datas selecionadas. Tente outras datas ou filtros.';
                $messageType = 'info';
            } else {
                $message = 'Encontramos ' . count($searchResults) . ' quarto(s) disponível(eis) para suas datas!';
                $messageType = 'success';
            }
        }
    }
}

// Buscar reservas do usuário (se logado)
$userReservations = [];
if ($auth->isLoggedIn()) {
    $userReservations = $db->fetchAll(
        "SELECT r.*, rt.name as type_name, rt.base_price, ro.room_number,
                (r.check_out - r.check_in) as nights,
                (rt.base_price * (r.check_out - r.check_in)) as calculated_total
         FROM reservations r
         JOIN rooms ro ON r.room_id = ro.id
         JOIN room_types rt ON ro.room_type_id = rt.id
         WHERE r.user_id = ?
         ORDER BY r.check_in DESC
         LIMIT 10",
        [$auth->getCurrentUser()['id']]
    );
}

// Buscar promoções ativas
$activePromotions = $db->fetchAll(
    "SELECT * FROM promotions 
     WHERE status = 'active' 
     AND valid_from <= CURDATE() 
     AND valid_until >= CURDATE() 
     AND (max_uses IS NULL OR current_uses < max_uses)
     ORDER BY discount_value DESC"
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades e Quartos - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/units.css">
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
                    <li><a href="accommodation.php">Hospedagens</a></li>
                    <li><a href="units.php" class="active">Unidades</a></li>
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
    <section class="units-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Nossas Unidades</h1>
                <p>Conheça nossos quartos e suítes exclusivos, cada um com sua personalidade única</p>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-card">
                <h2><i class="fas fa-search"></i> Buscar Disponibilidade</h2>
                <form method="POST" class="search-form" id="availabilitySearchForm">
                    <input type="hidden" name="action" value="search_availability">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in">
                                <i class="fas fa-calendar-check"></i>
                                Check-in *
                            </label>
                            <input type="date" id="check_in" name="check_in" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="check_out">
                                <i class="fas fa-calendar-times"></i>
                                Check-out *
                            </label>
                            <input type="date" id="check_out" name="check_out" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="guests">
                                <i class="fas fa-users"></i>
                                Hóspedes *
                            </label>
                            <select id="guests" name="guests" required>
                                <option value="1">1 Pessoa</option>
                                <option value="2" selected>2 Pessoas</option>
                                <option value="3">3 Pessoas</option>
                                <option value="4">4 Pessoas</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="room_type">
                                <i class="fas fa-bed"></i>
                                Tipo de Quarto
                            </label>
                            <select id="room_type" name="room_type">
                                <option value="">Todos os tipos</option>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Buscar Disponibilidade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="units-main">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : ($messageType === 'success' ? 'check-circle' : 'info-circle'); ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Promotions Section -->
            <?php if (!empty($activePromotions)): ?>
                <section class="promotions-section">
                    <h2><i class="fas fa-tags"></i> Promoções Ativas</h2>
                    <div class="promotions-grid">
                        <?php foreach ($activePromotions as $promotion): ?>
                            <div class="promotion-card">
                                <div class="promotion-header">
                                    <h3><?php echo htmlspecialchars($promotion['title']); ?></h3>
                                    <span class="discount-badge">
                                        <?php if ($promotion['discount_type'] === 'percentage'): ?>
                                            <?php echo $promotion['discount_value']; ?>% OFF
                                        <?php else: ?>
                                            R$ <?php echo number_format($promotion['discount_value'], 2, ',', '.'); ?> OFF
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p><?php echo htmlspecialchars($promotion['description']); ?></p>
                                <div class="promotion-details">
                                    <div class="detail">
                                        <i class="fas fa-calendar"></i>
                                        <span>Válido até: <?php echo date('d/m/Y', strtotime($promotion['valid_until'])); ?></span>
                                    </div>
                                    <?php if ($promotion['min_stay'] > 1): ?>
                                        <div class="detail">
                                            <i class="fas fa-moon"></i>
                                            <span>Mínimo: <?php echo $promotion['min_stay']; ?> noites</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Room Types Overview -->
            <section class="room-types-section">
                <h2><i class="fas fa-bed"></i> Tipos de Quartos</h2>
                <div class="room-types-grid">
                    <?php foreach ($roomTypes as $type): ?>
                        <div class="room-type-card">
                            <div class="type-image">
                                <img src="assets/images/rooms/<?php echo json_decode($type['images'], true)[0] ?? 'default-room.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($type['name']); ?>"
                                     onerror="this.src='assets/images/rooms/default-room.jpg'">
                                <div class="price-badge">
                                    R$ <?php echo number_format($type['base_price'], 2, ',', '.'); ?>/noite
                                </div>
                            </div>
                            
                            <div class="type-content">
                                <h3><?php echo htmlspecialchars($type['name']); ?></h3>
                                <p><?php echo htmlspecialchars($type['description']); ?></p>
                                
                                <div class="type-details">
                                    <div class="detail">
                                        <i class="fas fa-users"></i>
                                        <span>Até <?php echo $type['max_occupancy']; ?> pessoas</span>
                                    </div>
                                    
                                    <?php 
                                    $amenities = json_decode($type['amenities'], true);
                                    if ($amenities && count($amenities) > 0):
                                    ?>
                                        <div class="amenities">
                                            <strong>Comodidades:</strong>
                                            <div class="amenities-list">
                                                <?php foreach (array_slice($amenities, 0, 4) as $amenity): ?>
                                                    <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($amenities) > 4): ?>
                                                    <span class="amenity-tag more">+<?php echo count($amenities) - 4; ?> mais</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" class="btn btn-secondary" 
                                        onclick="scrollToSearch()">
                                    <i class="fas fa-calendar-plus"></i>
                                    Verificar Disponibilidade
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Search Results -->
            <?php if (!empty($searchResults)): ?>
                <section class="search-results-section">
                    <h2><i class="fas fa-list"></i> Quartos Disponíveis</h2>
                    <div class="search-results-grid">
                        <?php foreach ($searchResults as $room): ?>
                            <div class="room-card">
                                <div class="room-image">
                                    <img src="assets/images/rooms/<?php echo json_decode($room['images'], true)[0] ?? 'default-room.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($room['type_name']); ?>"
                                         onerror="this.src='assets/images/rooms/default-room.jpg'">
                                    <div class="room-number">Quarto <?php echo $room['room_number']; ?></div>
                                </div>
                                
                                <div class="room-content">
                                    <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($room['description']); ?></p>
                                    
                                    <div class="room-details">
                                        <div class="detail">
                                            <i class="fas fa-users"></i>
                                            <span>Até <?php echo $room['max_occupancy']; ?> pessoas</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo $room['nights']; ?> noite(s)</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-building"></i>
                                            <span><?php echo $room['floor']; ?>º andar</span>
                                        </div>
                                    </div>
                                    
                                    <div class="pricing">
                                        <div class="price-breakdown">
                                            <div class="price-item">
                                                <span>R$ <?php echo number_format($room['base_price'], 2, ',', '.'); ?> x <?php echo $room['nights']; ?> noite(s)</span>
                                                <span>R$ <?php echo number_format($room['base_price'] * $room['nights'], 2, ',', '.'); ?></span>
                                            </div>
                                            <div class="price-total">
                                                <span>Total</span>
                                                <span>R$ <?php echo number_format($room['base_price'] * $room['nights'], 2, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="room-actions">
                                        <?php if ($auth->isLoggedIn()): ?>
                                            <a href="reservations.php?room_id=<?php echo $room['id']; ?>&check_in=<?php echo $_POST['check_in'] ?? ''; ?>&check_out=<?php echo $_POST['check_out'] ?? ''; ?>&guests=<?php echo $_POST['guests'] ?? 1; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-calendar-check"></i>
                                                Reservar
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt"></i>
                                                Login para Reservar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-secondary" 
                                                onclick="addToCart('room', <?php echo $room['id']; ?>, <?php echo $room['base_price'] * $room['nights']; ?>)">
                                            <i class="fas fa-shopping-cart"></i>
                                            Adicionar ao Carrinho
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- User Reservations -->
            <?php if ($auth->isLoggedIn() && !empty($userReservations)): ?>
                <section class="user-reservations-section">
                    <h2><i class="fas fa-calendar-check"></i> Suas Reservas</h2>
                    <div class="reservations-list">
                        <?php foreach ($userReservations as $reservation): ?>
                            <div class="reservation-card">
                                <div class="reservation-info">
                                    <div class="reservation-header">
                                        <h3><?php echo htmlspecialchars($reservation['type_name']); ?></h3>
                                        <span class="status status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="reservation-details">
                                        <div class="detail">
                                            <i class="fas fa-door-open"></i>
                                            <span>Quarto <?php echo $reservation['room_number']; ?></span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('d/m/Y', strtotime($reservation['check_in'])); ?> - <?php echo date('d/m/Y', strtotime($reservation['check_out'])); ?></span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-moon"></i>
                                            <span><?php echo $reservation['nights']; ?> noite(s)</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $reservation['guests']; ?> pessoa(s)</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>R$ <?php echo number_format($reservation['total_amount'], 2, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="reservation-actions">
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <a href="reservations.php?edit=<?php echo $reservation['id']; ?>" 
                                           class="btn btn-secondary btn-small">
                                            Editar
                                        </a>
                                        <button type="button" class="btn btn-danger btn-small" 
                                                onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="reservations.php?view=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-outline btn-small">
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

<?php
// Include footer
include 'includes/footer.php';
?>
