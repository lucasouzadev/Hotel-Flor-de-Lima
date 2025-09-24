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

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do pedido
$input = json_decode(file_get_contents('php://input'), true);
$orderItems = $input['items'] ?? [];
$tableNumber = $input['table_number'] ?? null;
$notes = $input['notes'] ?? '';

if (empty($orderItems)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum item no pedido']);
    exit;
}

try {
    // Calcular total do pedido
    $totalAmount = 0;
    $validItems = [];
    
    foreach ($orderItems as $item) {
        $drinkId = (int)$item['drink_id'];
        $quantity = (int)$item['quantity'];
        
        if ($drinkId <= 0 || $quantity <= 0) {
            continue;
        }
        
        // Buscar informações do drink
        $drink = $db->fetch("SELECT * FROM drinks WHERE id = ? AND is_available = 1", [$drinkId]);
        
        if (!$drink) {
            echo json_encode(['success' => false, 'message' => "Drink ID $drinkId não encontrado ou indisponível"]);
            exit;
        }
        
        $itemTotal = $drink['price'] * $quantity;
        $totalAmount += $itemTotal;
        
        $validItems[] = [
            'drink_id' => $drinkId,
            'quantity' => $quantity,
            'unit_price' => $drink['price'],
            'total_price' => $itemTotal,
            'drink_name' => $drink['name']
        ];
    }
    
    if (empty($validItems)) {
        echo json_encode(['success' => false, 'message' => 'Nenhum item válido no pedido']);
        exit;
    }
    
    // Inserir pedido
    $orderData = [
        'user_id' => $auth->getCurrentUser()['id'],
        'table_number' => $tableNumber,
        'total_amount' => $totalAmount,
        'notes' => $notes,
        'status' => 'pending'
    ];
    
    $orderId = $db->insert('bar_orders', $orderData);
    
    // Inserir itens do pedido
    foreach ($validItems as $item) {
        $itemData = [
            'order_id' => $orderId,
            'drink_id' => $item['drink_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['total_price']
        ];
        
        $db->insert('bar_order_items', $itemData);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido realizado com sucesso!',
        'order_id' => $orderId,
        'total_amount' => $totalAmount,
        'items_count' => count($validItems)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar pedido: ' . $e->getMessage()
    ]);
}
?>
