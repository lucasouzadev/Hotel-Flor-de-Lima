<?php
// Verificar se a sessão já está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($name, $email, $password, $phone = null, $birth_date = null, $document = null) {
        // Verificar se o email já existe
        $existingUser = $this->db->fetch(
            "SELECT id FROM users WHERE email = ?", 
            [$email]
        );
        
        if ($existingUser) {
            return ['success' => false, 'message' => 'Este email já está cadastrado.'];
        }
        
        // Validar dados
        $validation = $this->validateRegistrationData($name, $email, $password, $phone, $birth_date, $document);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Hash da senha
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $userId = $this->db->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'phone' => $phone,
                'birth_date' => $birth_date,
                'document' => $document
            ]);
            
            return ['success' => true, 'message' => 'Usuário cadastrado com sucesso!', 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao cadastrar usuário: ' . $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        // Buscar usuário
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND status = 'active'", 
            [$email]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email ou senha incorretos.'];
        }
        
        // Verificar senha
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email ou senha incorretos.'];
        }
        
        // Criar sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        return ['success' => true, 'message' => 'Login realizado com sucesso!', 'user' => $user];
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout realizado com sucesso!'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }
    
    public function updatePassword($userId, $currentPassword, $newPassword) {
        // Buscar usuário
        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuário não encontrado.'];
        }
        
        // Verificar senha atual
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Senha atual incorreta.'];
        }
        
        // Validar nova senha
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'];
        }
        
        // Atualizar senha
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = $this->db->update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Senha atualizada com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Erro ao atualizar senha.'];
        }
    }
    
    public function updateProfile($userId, $name, $phone, $birth_date, $document) {
        $data = [
            'name' => $name,
            'phone' => $phone,
            'birth_date' => $birth_date,
            'document' => $document
        ];
        
        $result = $this->db->update('users', $data, 'id = ?', [$userId]);
        
        if ($result) {
            // Atualizar sessão
            $_SESSION['user_name'] = $name;
            return ['success' => true, 'message' => 'Perfil atualizado com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Erro ao atualizar perfil.'];
        }
    }
    
    private function validateRegistrationData($name, $email, $password, $phone, $birth_date, $document) {
        // Validar nome
        if (empty($name) || strlen($name) < 2) {
            return ['valid' => false, 'message' => 'Nome deve ter pelo menos 2 caracteres.'];
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email inválido.'];
        }
        
        // Validar senha
        if (strlen($password) < 6) {
            return ['valid' => false, 'message' => 'Senha deve ter pelo menos 6 caracteres.'];
        }
        
        // Validar telefone (se fornecido)
        if ($phone && !preg_match('/^\(\d{2}\)\s\d{4,5}-\d{4}$/', $phone)) {
            return ['valid' => false, 'message' => 'Formato de telefone inválido. Use (XX) XXXXX-XXXX.'];
        }
        
        // Validar data de nascimento (se fornecida)
        if ($birth_date) {
            $date = DateTime::createFromFormat('Y-m-d', $birth_date);
            if (!$date || $date->format('Y-m-d') !== $birth_date) {
                return ['valid' => false, 'message' => 'Data de nascimento inválida.'];
            }
            
            // Verificar se é maior de 18 anos
            $today = new DateTime();
            $age = $today->diff($date)->y;
            if ($age < 18) {
                return ['valid' => false, 'message' => 'Você deve ter pelo menos 18 anos para se cadastrar.'];
            }
        }
        
        return ['valid' => true];
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        
        // Aqui você pode implementar verificação de admin
        // Por enquanto, vamos assumir que todos os usuários logados podem acessar
        return true;
    }
}

// Funções auxiliares globais
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>
