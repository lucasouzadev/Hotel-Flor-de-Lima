<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Require login para fazer reservas
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Buscar tipos de quartos disponíveis
$roomTypes = $db->fetchAll("SELECT * FROM room_types ORDER BY base_price");

// Processar reserva
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'make_reservation') {
    $roomTypeId = (int)$_POST['room_type_id'];
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $specialRequests = trim($_POST['special_requests'] ?? '');
    
    // Validar dados
    if (empty($roomTypeId) || empty($checkIn) || empty($checkOut) || empty($guests)) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'error';
    } else {
        // Validar datas
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $today = new DateTime();
        
        if ($checkInDate < $today) {
            $message = 'A data de check-in não pode ser no passado.';
            $messageType = 'error';
        } elseif ($checkOutDate <= $checkInDate) {
            $message = 'A data de check-out deve ser posterior ao check-in.';
            $messageType = 'error';
        } elseif ($guests < 1 || $guests > 4) {
            $message = 'O número de hóspedes deve ser entre 1 e 4.';
            $messageType = 'error';
        } else {
            // Buscar quartos disponíveis
            $availableRooms = $db->fetchAll(
                "SELECT r.* FROM rooms r 
                 JOIN room_types rt ON r.room_type_id = rt.id 
                 WHERE r.room_type_id = ? AND r.status = 'available' 
                 AND r.id NOT IN (
                     SELECT res.room_id FROM reservations res 
                     WHERE res.status IN ('pending', 'confirmed') 
                     AND ((res.check_in <= ? AND res.check_out > ?) 
                          OR (res.check_in < ? AND res.check_out >= ?))
                 )",
                [$roomTypeId, $checkOut, $checkIn, $checkOut, $checkIn]
            );
            
            if (empty($availableRooms)) {
                $message = 'Não há quartos disponíveis para as datas selecionadas.';
                $messageType = 'error';
            } else {
                // Calcular preço total
                $roomType = $db->fetch("SELECT * FROM room_types WHERE id = ?", [$roomTypeId]);
                $nights = $checkInDate->diff($checkOutDate)->days;
                $totalAmount = $roomType['base_price'] * $nights;
                
                // Criar reserva
                try {
                    $reservationId = $db->insert('reservations', [
                        'user_id' => $currentUser['id'],
                        'room_id' => $availableRooms[0]['id'],
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'guests' => $guests,
                        'total_amount' => $totalAmount,
                        'special_requests' => $specialRequests,
                        'status' => 'pending'
                    ]);
                    
                    $message = "Reserva criada com sucesso! ID da reserva: #{$reservationId}";
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

// Buscar reservas do usuário
$userReservations = $db->fetchAll(
    "SELECT r.*, rt.name as room_type, rt.base_price, rm.room_number, rm.floor,
            rt.amenities, rt.images
     FROM reservations r
     JOIN rooms rm ON r.room_id = rm.id
     JOIN room_types rt ON rm.room_type_id = rt.id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    [$currentUser['id']]
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/reservations.css">
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
                    <li><a href="reservations.php" class="active">Reservas</a></li>
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
    <section class="reservations-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Fazer Reserva</h1>
                <p>Escolha seu quarto ideal e garante sua estadia no Hotel Flor de Lima</p>
            </div>
        </div>
    </section>

    <!-- Reservation Form Section -->
    <section class="reservation-form-section">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="reservation-content">
                <div class="form-container">
                    <h2>Nova Reserva</h2>
                    <form method="POST" class="reservation-form" id="reservationForm">
                        <input type="hidden" name="action" value="make_reservation">
                        
                        <div class="form-group">
                            <label for="room_type_id">
                                <i class="fas fa-bed"></i>
                                Tipo de Quarto *
                            </label>
                            <select id="room_type_id" name="room_type_id" required onchange="updateRoomInfo()">
                                <option value="">Selecione um tipo de quarto</option>
                                <?php foreach ($roomTypes as $roomType): ?>
                                    <option value="<?php echo $roomType['id']; ?>" 
                                            data-price="<?php echo $roomType['base_price']; ?>"
                                            data-max-occupancy="<?php echo $roomType['max_occupancy']; ?>"
                                            data-amenities="<?php echo htmlspecialchars($roomType['amenities']); ?>">
                                        <?php echo htmlspecialchars($roomType['name']); ?> - 
                                        R$ <?php echo number_format($roomType['base_price'], 2, ',', '.'); ?>/noite
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="check_in">
                                    <i class="fas fa-calendar-check"></i>
                                    Check-in *
                                </label>
                                <input type="date" id="check_in" name="check_in" 
                                       value="<?php echo htmlspecialchars($_POST['check_in'] ?? ''); ?>" 
                                       required onchange="calculateTotal()">
                            </div>
                            
                            <div class="form-group">
                                <label for="check_out">
                                    <i class="fas fa-calendar-times"></i>
                                    Check-out *
                                </label>
                                <input type="date" id="check_out" name="check_out" 
                                       value="<?php echo htmlspecialchars($_POST['check_out'] ?? ''); ?>" 
                                       required onchange="calculateTotal()">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="guests">
                                <i class="fas fa-users"></i>
                                Número de Hóspedes *
                            </label>
                            <select id="guests" name="guests" required onchange="validateGuests()">
                                <option value="">Selecione o número de hóspedes</option>
                                <option value="1" <?php echo ($_POST['guests'] ?? '') == '1' ? 'selected' : ''; ?>>1 hóspede</option>
                                <option value="2" <?php echo ($_POST['guests'] ?? '') == '2' ? 'selected' : ''; ?>>2 hóspedes</option>
                                <option value="3" <?php echo ($_POST['guests'] ?? '') == '3' ? 'selected' : ''; ?>>3 hóspedes</option>
                                <option value="4" <?php echo ($_POST['guests'] ?? '') == '4' ? 'selected' : ''; ?>>4 hóspedes</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="special_requests">
                                <i class="fas fa-comment"></i>
                                Solicitações Especiais
                            </label>
                            <textarea id="special_requests" name="special_requests" rows="3" 
                                      placeholder="Alguma solicitação especial? (opcional)"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="room-info" id="roomInfo" style="display: none;">
                            <h3>Informações do Quarto</h3>
                            <div class="room-details">
                                <div class="room-amenities">
                                    <h4>Comodidades:</h4>
                                    <ul id="amenitiesList"></ul>
                                </div>
                                <div class="room-price-info">
                                    <div class="price-breakdown">
                                        <div class="price-item">
                                            <span>Preço por noite:</span>
                                            <span id="pricePerNight">R$ 0,00</span>
                                        </div>
                                        <div class="price-item">
                                            <span>Número de noites:</span>
                                            <span id="numberOfNights">0</span>
                                        </div>
                                        <div class="price-item total">
                                            <span>Total:</span>
                                            <span id="totalAmount">R$ 0,00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                            <i class="fas fa-calendar-plus"></i>
                            Fazer Reserva
                        </button>
                    </form>
                </div>
                
                <div class="room-types-preview">
                    <h2>Tipos de Quartos</h2>
                    <div class="room-types-grid">
                        <?php foreach ($roomTypes as $roomType): ?>
                            <div class="room-type-card" data-room-type-id="<?php echo $roomType['id']; ?>">
                                <div class="room-type-image">
                                    <img src="assets/images/rooms/<?php echo json_decode($roomType['images'], true)[0] ?? 'default-room.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($roomType['name']); ?>"
                                         onerror="this.src='assets/images/rooms/default-room.jpg'">
                                </div>
                                <div class="room-type-info">
                                    <h3><?php echo htmlspecialchars($roomType['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($roomType['description']); ?></p>
                                    <div class="room-type-details">
                                        <div class="detail">
                                            <i class="fas fa-users"></i>
                                            <span>Até <?php echo $roomType['max_occupancy']; ?> pessoas</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>R$ <?php echo number_format($roomType['base_price'], 2, ',', '.'); ?>/noite</span>
                                        </div>
                                    </div>
                                    <div class="amenities-preview">
                                        <?php 
                                        $amenities = json_decode($roomType['amenities'], true);
                                        if ($amenities && count($amenities) > 0):
                                        ?>
                                            <div class="amenities-list">
                                                <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                                    <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($amenities) > 3): ?>
                                                    <span class="amenity-tag more">+<?php echo count($amenities) - 3; ?> mais</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Reservations Section -->
    <section class="user-reservations">
        <div class="container">
            <h2>Suas Reservas</h2>
            
            <?php if (empty($userReservations)): ?>
                <div class="no-reservations">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Nenhuma reserva encontrada</h3>
                    <p>Você ainda não fez nenhuma reserva. Use o formulário acima para fazer sua primeira reserva!</p>
                </div>
            <?php else: ?>
                <div class="reservations-list">
                    <?php foreach ($userReservations as $reservation): ?>
                        <div class="reservation-card">
                            <div class="reservation-header">
                                <div class="reservation-id">
                                    <h3>Reserva #<?php echo $reservation['id']; ?></h3>
                                    <span class="status status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </div>
                                <div class="reservation-date">
                                    <small>Criada em: <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="reservation-details">
                                <div class="detail-row">
                                    <div class="detail">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo htmlspecialchars($reservation['room_type']); ?> - Quarto <?php echo $reservation['room_number']; ?></span>
                                    </div>
                                    <div class="detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $reservation['guests']; ?> hóspede(s)</span>
                                    </div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Check-in: <?php echo date('d/m/Y', strtotime($reservation['check_in'])); ?></span>
                                    </div>
                                    <div class="detail">
                                        <i class="fas fa-calendar-times"></i>
                                        <span>Check-out: <?php echo date('d/m/Y', strtotime($reservation['check_out'])); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($reservation['special_requests']): ?>
                                    <div class="detail-row">
                                        <div class="detail">
                                            <i class="fas fa-comment"></i>
                                            <span>Solicitações: <?php echo htmlspecialchars($reservation['special_requests']); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="detail-row">
                                    <div class="detail">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Total: R$ <?php echo number_format($reservation['total_amount'], 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reservation-actions">
                                <?php if ($reservation['status'] === 'pending'): ?>
                                    <button type="button" class="btn btn-secondary" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($reservation['status'] === 'confirmed' && strtotime($reservation['check_in']) <= time()): ?>
                                    <button type="button" class="btn btn-primary" onclick="checkIn(<?php echo $reservation['id']; ?>)">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Check-in
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
    <script src="assets/js/reservations.js"></script>
</body>
</html>
