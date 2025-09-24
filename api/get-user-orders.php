<?php
// Desabilitar exibição de erros para evitar quebrar o JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Verificar se a sessão já está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth();
$db = new Database();

// Verificar se o usuário está logado
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

try {
    $userId = $auth->getCurrentUser()['id'];
    
    // Buscar pedidos do bar
    $barOrders = $db->fetchAll(
        "SELECT bo.*, 
                COUNT(boi.id) as items_count,
                GROUP_CONCAT(d.name SEPARATOR ', ') as drinks_names
         FROM bar_orders bo
         LEFT JOIN bar_order_items boi ON bo.id = boi.order_id
         LEFT JOIN drinks d ON boi.drink_id = d.id
         WHERE bo.user_id = ?
         GROUP BY bo.id
         ORDER BY bo.order_time DESC
         LIMIT 10",
        [$userId]
    );
    
    // Buscar reservas
    $reservations = $db->fetchAll(
        "SELECT r.*, rt.name as type_name, ro.room_number
         FROM reservations r
         JOIN rooms ro ON r.room_id = ro.id
         JOIN room_types rt ON ro.room_type_id = rt.id
         WHERE r.user_id = ?
         ORDER BY r.check_in DESC
         LIMIT 10",
        [$userId]
    );
    
    // Buscar reservas de áreas de lazer
    $leisureReservations = $db->fetchAll(
        "SELECT lr.*, la.name as area_name
         FROM leisure_reservations lr
         JOIN leisure_areas la ON lr.area_id = la.id
         WHERE lr.user_id = ?
         ORDER BY lr.reservation_date DESC
         LIMIT 10",
        [$userId]
    );
    
    echo json_encode([
        'success' => true,
        'data' => [
            'bar_orders' => $barOrders,
            'reservations' => $reservations,
            'leisure_reservations' => $leisureReservations
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
?>
