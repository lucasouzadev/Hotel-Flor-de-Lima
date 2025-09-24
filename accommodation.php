<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Page configuration
$pageTitle = 'Hospedagens e Áreas de Lazer';
$pageDescription = 'Desfrute de nossas instalações exclusivas durante sua estadia no Hotel Flor de Lima';
$additionalCSS = ['assets/css/accommodation.css'];
$additionalJS = ['assets/js/accommodation.js'];

// Buscar áreas de lazer disponíveis
$leisureAreas = $db->fetchAll("SELECT * FROM leisure_areas WHERE status = 'available' ORDER BY name");

// Processar reserva de área de lazer
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'reserve_leisure') {
    if (!$auth->isLoggedIn()) {
        $message = 'Você precisa fazer login para fazer reservas.';
        $messageType = 'error';
    } else {
        $areaId = (int)$_POST['area_id'];
        $reservationDate = $_POST['reservation_date'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $guests = (int)$_POST['guests'];
        
        // Validar dados
        if (empty($areaId) || empty($reservationDate) || empty($startTime) || empty($endTime) || empty($guests)) {
            $message = 'Por favor, preencha todos os campos.';
            $messageType = 'error';
        } else {
            // Validar data (não pode ser no passado)
            $selectedDate = new DateTime($reservationDate);
            $today = new DateTime();
            
            if ($selectedDate < $today) {
                $message = 'A data da reserva não pode ser no passado.';
                $messageType = 'error';
            } elseif ($startTime >= $endTime) {
                $message = 'O horário de fim deve ser posterior ao de início.';
                $messageType = 'error';
            } else {
                // Verificar disponibilidade
                $existingReservation = $db->fetch(
                    "SELECT id FROM leisure_reservations 
                     WHERE area_id = ? AND reservation_date = ? 
                     AND status IN ('pending', 'confirmed')
                     AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))",
                    [$areaId, $reservationDate, $startTime, $startTime, $endTime, $endTime]
                );
                
                if ($existingReservation) {
                    $message = 'Este horário já está reservado. Escolha outro horário.';
                    $messageType = 'error';
                } else {
                    // Criar reserva
                    try {
                        $reservationId = $db->insert('leisure_reservations', [
                            'user_id' => $auth->getCurrentUser()['id'],
                            'area_id' => $areaId,
                            'reservation_date' => $reservationDate,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'guests' => $guests,
                            'status' => 'pending'
                        ]);
                        
                        $message = "Reserva de área de lazer criada com sucesso! ID: #{$reservationId}";
                        $messageType = 'success';
                        
                        // Limpar formulário
                        $_POST = [];
                    } catch (Exception $e) {
                        $message = 'Erro ao criar reserva: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }
        }
    }
}

// Buscar reservas do usuário (se logado)
$userLeisureReservations = [];
if ($auth->isLoggedIn()) {
    $userLeisureReservations = $db->fetchAll(
        "SELECT lr.*, la.name as area_name, la.description as area_description
         FROM leisure_reservations lr
         JOIN leisure_areas la ON lr.area_id = la.id
         WHERE lr.user_id = ?
         ORDER BY lr.reservation_date DESC, lr.start_time DESC
         LIMIT 10",
        [$auth->getCurrentUser()['id']]
    );
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospedagens e Áreas de Lazer - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/accommodation.css">
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
                    <li><a href="accommodation.php" class="active">Hospedagens</a></li>
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
    <section class="accommodation-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Hospedagens e Áreas de Lazer</h1>
                <p>Desfrute de nossas instalações exclusivas durante sua estadia</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="accommodation-main">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Leisure Areas Section -->
            <section class="leisure-areas-section">
                <h2><i class="fas fa-swimming-pool"></i> Áreas de Lazer</h2>
                
                <?php if (empty($leisureAreas)): ?>
                    <div class="no-areas">
                        <i class="fas fa-tools"></i>
                        <h3>Áreas em Manutenção</h3>
                        <p>Nossas áreas de lazer estão temporariamente indisponíveis. Volte em breve!</p>
                    </div>
                <?php else: ?>
                    <div class="leisure-areas-grid">
                        <?php foreach ($leisureAreas as $area): ?>
                            <div class="leisure-area-card">
                                <div class="area-image">
                                    <img src="assets/images/leisure/<?php echo json_decode($area['images'], true)[0] ?? 'default-leisure.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($area['name']); ?>"
                                         onerror="this.src='assets/images/leisure/default-leisure.jpg'">
                                    <div class="area-status">
                                        <i class="fas fa-check-circle"></i>
                                        Disponível
                                    </div>
                                </div>
                                
                                <div class="area-content">
                                    <h3><?php echo htmlspecialchars($area['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($area['description']); ?></p>
                                    
                                    <div class="area-details">
                                        <div class="detail">
                                            <i class="fas fa-users"></i>
                                            <span>Capacidade: <?php echo $area['capacity']; ?> pessoas</span>
                                        </div>
                                        
                                        <?php 
                                        $amenities = json_decode($area['amenities'], true);
                                        if ($amenities && count($amenities) > 0):
                                        ?>
                                            <div class="amenities">
                                                <strong>Comodidades:</strong>
                                                <div class="amenities-list">
                                                    <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                                        <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($amenities) > 3): ?>
                                                        <span class="amenity-tag more">+<?php echo count($amenities) - 3; ?> mais</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $hours = json_decode($area['operating_hours'], true);
                                        if ($hours):
                                        ?>
                                            <div class="operating-hours">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $hours['open']; ?> - <?php echo $hours['close']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="button" class="btn btn-primary reserve-area-btn" 
                                            onclick="openReservationModal(<?php echo $area['id']; ?>, '<?php echo htmlspecialchars($area['name']); ?>', <?php echo $area['capacity']; ?>)">
                                        <i class="fas fa-calendar-plus"></i>
                                        Reservar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- User Reservations Section -->
            <?php if ($auth->isLoggedIn() && !empty($userLeisureReservations)): ?>
                <section class="user-reservations-section">
                    <h2><i class="fas fa-calendar-check"></i> Suas Reservas de Áreas de Lazer</h2>
                    
                    <div class="reservations-list">
                        <?php foreach ($userLeisureReservations as $reservation): ?>
                            <div class="reservation-card">
                                <div class="reservation-info">
                                    <div class="reservation-header">
                                        <h3><?php echo htmlspecialchars($reservation['area_name']); ?></h3>
                                        <span class="status status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="reservation-details">
                                        <div class="detail">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('H:i', strtotime($reservation['start_time'])); ?> - <?php echo date('H:i', strtotime($reservation['end_time'])); ?></span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $reservation['guests']; ?> pessoa(s)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="reservation-actions">
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-secondary btn-small" 
                                                onclick="cancelLeisureReservation(<?php echo $reservation['id']; ?>)">
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Reservation Modal -->
    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reservar Área de Lazer</h3>
                <span class="close" onclick="closeReservationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" class="reservation-form" id="leisureReservationForm">
                    <input type="hidden" name="action" value="reserve_leisure">
                    <input type="hidden" name="area_id" id="modal_area_id">
                    
                    <div class="area-info" id="modal_area_info">
                        <h4 id="modal_area_name"></h4>
                        <p>Capacidade máxima: <span id="modal_area_capacity"></span> pessoas</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="reservation_date">
                            <i class="fas fa-calendar"></i>
                            Data da Reserva *
                        </label>
                        <input type="date" id="reservation_date" name="reservation_date" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_time">
                                <i class="fas fa-clock"></i>
                                Horário de Início *
                            </label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time">
                                <i class="fas fa-clock"></i>
                                Horário de Fim *
                            </label>
                            <input type="time" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="guests">
                            <i class="fas fa-users"></i>
                            Número de Pessoas *
                        </label>
                        <input type="number" id="guests" name="guests" min="1" max="50" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeReservationModal()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-plus"></i>
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <li><a href="accommodation.php">Hospedagens</a></li>
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
    <script src="assets/js/accommodation.js"></script>
</body>
</html>
