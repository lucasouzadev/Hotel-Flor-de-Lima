<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Require login
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Buscar dados do usuário
$userReservations = $db->fetchAll(
    "SELECT r.*, rt.name as room_type, rm.room_number, rm.floor,
            rt.amenities, rt.images
     FROM reservations r
     JOIN rooms rm ON r.room_id = rm.id
     JOIN room_types rt ON rm.room_type_id = rt.id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC
     LIMIT 5",
    [$currentUser['id']]
);

$userBarOrders = $db->fetchAll(
    "SELECT bo.*, GROUP_CONCAT(CONCAT(boi.quantity, 'x ', d.name) SEPARATOR ', ') as items
     FROM bar_orders bo
     LEFT JOIN bar_order_items boi ON bo.id = boi.order_id
     LEFT JOIN drinks d ON boi.drink_id = d.id
     WHERE bo.user_id = ?
     GROUP BY bo.id
     ORDER BY bo.order_time DESC
     LIMIT 5",
    [$currentUser['id']]
);

$userFeedbacks = $db->fetchAll(
    "SELECT * FROM feedbacks 
     WHERE user_id = ? 
     ORDER BY created_at DESC 
     LIMIT 5",
    [$currentUser['id']]
);

$userComments = $db->fetchAll(
    "SELECT c.*, na.title as article_title
     FROM comments c
     LEFT JOIN newspaper_articles na ON c.article_id = na.id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC
     LIMIT 5",
    [$currentUser['id']]
);

// Estatísticas do usuário
$stats = [
    'total_reservations' => $db->fetch("SELECT COUNT(*) as count FROM reservations WHERE user_id = ?", [$currentUser['id']])['count'],
    'total_spent' => $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM reservations WHERE user_id = ? AND status = 'completed'", [$currentUser['id']])['total'],
    'total_feedbacks' => $db->fetch("SELECT COUNT(*) as count FROM feedbacks WHERE user_id = ?", [$currentUser['id']])['count'],
    'member_since' => $db->fetch("SELECT created_at FROM users WHERE id = ?", [$currentUser['id']])['created_at']
];

// Processar atualização de perfil
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $document = trim($_POST['document'] ?? '');
    
    if (empty($name)) {
        $message = 'Nome é obrigatório.';
        $messageType = 'error';
    } else {
        try {
            $result = $auth->updateProfile($currentUser['id'], $name, $phone, $birth_date, $document);
            
            if ($result['success']) {
                $message = 'Perfil atualizado com sucesso!';
                $messageType = 'success';
                
                // Atualizar dados do usuário
                $currentUser = $auth->getCurrentUser();
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Erro ao atualizar perfil: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Processar alteração de senha
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'Todos os campos de senha são obrigatórios.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'As senhas não coincidem.';
        $messageType = 'error';
    } else {
        try {
            $result = $auth->updatePassword($currentUser['id'], $currentPassword, $newPassword);
            
            if ($result['success']) {
                $message = 'Senha alterada com sucesso!';
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Erro ao alterar senha: ' . $e->getMessage();
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
    <title>Dashboard - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="logout.php">Sair</a></li>
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
    <section class="dashboard-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Bem-vindo, <?php echo htmlspecialchars($currentUser['name']); ?>!</h1>
                <p>Gerencie suas reservas, pedidos e preferências</p>
            </div>
        </div>
    </section>

    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_reservations']; ?></div>
                            <div class="stat-label">Reservas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number">R$ <?php echo number_format($stats['total_spent'], 2, ',', '.'); ?></div>
                            <div class="stat-label">Total Gasto</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_feedbacks']; ?></div>
                            <div class="stat-label">Feedbacks</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo date('M Y', strtotime($stats['member_since'])); ?></div>
                            <div class="stat-label">Membro Desde</div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Main Dashboard Layout -->
            <div class="dashboard-layout">
                <!-- Left Column -->
                <div class="dashboard-main">
                    <!-- Quick Actions -->
                    <section class="quick-actions">
                        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                        <div class="actions-grid">
                            <a href="reservations.php" class="action-card">
                                <i class="fas fa-bed"></i>
                                <span>Nova Reserva</span>
                            </a>
                            <a href="bar-celina.php" class="action-card">
                                <i class="fas fa-cocktail"></i>
                                <span>Bar Celina</span>
                            </a>
                            <a href="newspaper.php" class="action-card">
                                <i class="fas fa-newspaper"></i>
                                <span>O CORVO</span>
                            </a>
                            <a href="newspaper.php#feedback" class="action-card">
                                <i class="fas fa-comment"></i>
                                <span>Deixar Feedback</span>
                            </a>
                        </div>
                    </section>
                    
                    <!-- Recent Reservations -->
                    <section class="recent-section">
                        <div class="section-header">
                            <h2><i class="fas fa-calendar-check"></i> Reservas Recentes</h2>
                            <a href="reservations.php" class="view-all">Ver todas</a>
                        </div>
                        
                        <?php if (empty($userReservations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>Nenhuma reserva encontrada</h3>
                                <p>Faça sua primeira reserva no Hotel Flor de Lima!</p>
                                <a href="reservations.php" class="btn btn-primary">Fazer Reserva</a>
                            </div>
                        <?php else: ?>
                            <div class="reservations-list">
                                <?php foreach ($userReservations as $reservation): ?>
                                    <div class="reservation-item">
                                        <div class="reservation-info">
                                            <div class="reservation-id">
                                                <strong>Reserva #<?php echo $reservation['id']; ?></strong>
                                                <span class="status status-<?php echo $reservation['status']; ?>">
                                                    <?php echo ucfirst($reservation['status']); ?>
                                                </span>
                                            </div>
                                            <div class="reservation-details">
                                                <div class="detail">
                                                    <i class="fas fa-bed"></i>
                                                    <span><?php echo htmlspecialchars($reservation['room_type']); ?> - Quarto <?php echo $reservation['room_number']; ?></span>
                                                </div>
                                                <div class="detail">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><?php echo date('d/m/Y', strtotime($reservation['check_in'])); ?> - <?php echo date('d/m/Y', strtotime($reservation['check_out'])); ?></span>
                                                </div>
                                                <div class="detail">
                                                    <i class="fas fa-dollar-sign"></i>
                                                    <span>R$ <?php echo number_format($reservation['total_amount'], 2, ',', '.'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reservation-actions">
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-secondary btn-small" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                                    Cancelar
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reservation['status'] === 'confirmed' && strtotime($reservation['check_in']) <= time()): ?>
                                                <button type="button" class="btn btn-primary btn-small" onclick="checkIn(<?php echo $reservation['id']; ?>)">
                                                    Check-in
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                    
                    <!-- Recent Bar Orders -->
                    <section class="recent-section">
                        <div class="section-header">
                            <h2><i class="fas fa-cocktail"></i> Pedidos do Bar</h2>
                            <a href="bar-celina.php" class="view-all">Ver cardápio</a>
                        </div>
                        
                        <?php if (empty($userBarOrders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-glass-martini-alt"></i>
                                <h3>Nenhum pedido encontrado</h3>
                                <p>Explore nossa carta de drinks no Bar Celina!</p>
                                <a href="bar-celina.php" class="btn btn-primary">Ver Cardápio</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($userBarOrders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <div class="order-id">
                                                <strong>Pedido #<?php echo $order['id']; ?></strong>
                                                <span class="status status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                            <div class="order-details">
                                                <div class="detail">
                                                    <i class="fas fa-list"></i>
                                                    <span><?php echo htmlspecialchars($order['items']); ?></span>
                                                </div>
                                                <div class="detail">
                                                    <i class="fas fa-dollar-sign"></i>
                                                    <span>R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></span>
                                                </div>
                                                <div class="detail">
                                                    <i class="fas fa-clock"></i>
                                                    <span><?php echo date('d/m/Y H:i', strtotime($order['order_time'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
                
                <!-- Right Sidebar -->
                <aside class="dashboard-sidebar">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info">
                                <h3><?php echo htmlspecialchars($currentUser['name']); ?></h3>
                                <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                            </div>
                        </div>
                        <div class="profile-actions">
                            <button type="button" class="btn btn-secondary btn-small" onclick="toggleModal('profileModal')">
                                <i class="fas fa-edit"></i>
                                Editar Perfil
                            </button>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="activity-card">
                        <h3><i class="fas fa-history"></i> Atividade Recente</h3>
                        <div class="activity-list">
                            <?php if (!empty($userComments)): ?>
                                <?php foreach (array_slice($userComments, 0, 3) as $comment): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-comment"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Comentário em <?php echo $comment['article_title'] ?: 'O CORVO'; ?></p>
                                            <small><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($userFeedbacks)): ?>
                                <?php foreach (array_slice($userFeedbacks, 0, 2) as $feedback): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Feedback: <?php echo htmlspecialchars($feedback['title'] ?: 'Sem título'); ?></p>
                                            <small><?php echo date('d/m/Y', strtotime($feedback['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="quick-links-card">
                        <h3><i class="fas fa-link"></i> Links Rápidos</h3>
                        <div class="links-list">
                            <a href="reservations.php" class="link-item">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Nova Reserva</span>
                            </a>
                            <a href="bar-celina.php" class="link-item">
                                <i class="fas fa-cocktail"></i>
                                <span>Bar Celina</span>
                            </a>
                            <a href="newspaper.php" class="link-item">
                                <i class="fas fa-newspaper"></i>
                                <span>O CORVO</span>
                            </a>
                            <a href="logout.php" class="link-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sair</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar Perfil</h3>
                <span class="close" onclick="toggleModal('profileModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Nome Completo *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Telefone</label>
                        <input type="tel" id="phone" name="phone" placeholder="(XX) XXXXX-XXXX">
                    </div>
                    
                    <div class="form-group">
                        <label for="birth_date">Data de Nascimento</label>
                        <input type="date" id="birth_date" name="birth_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="document">CPF</label>
                        <input type="text" id="document" name="document" placeholder="XXX.XXX.XXX-XX">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="toggleModal('profileModal')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Alterar Senha</h3>
                <span class="close" onclick="toggleModal('passwordModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" class="password-form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Senha Atual *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nova Senha *</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nova Senha *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="toggleModal('passwordModal')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Alterar Senha</button>
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
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
