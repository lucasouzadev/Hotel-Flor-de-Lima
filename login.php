<?php
require_once 'includes/auth.php';

$auth = new Auth();
$message = '';
$messageType = '';

// Se já estiver logado, redirecionar para dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Processar login
if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = 'Por favor, preencha todos os campos.';
        $messageType = 'error';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $message = $result['message'];
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
    <title>Login - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Hotel Flor de Lima</h1>
                <h2>Entrar na sua conta</h2>
                <p>Faça login para acessar sua conta e fazer reservas</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Senha
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Lembrar de mim
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Esqueci minha senha</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao início
                </a>
            </div>
        </div>
        
        <div class="auth-image">
            <img src="assets/images/hotel-login.jpg" alt="Hotel Flor de Lima">
            <div class="auth-overlay">
                <h3>Bem-vindo de volta!</h3>
                <p>Entre na sua conta para acessar todos os nossos serviços exclusivos.</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
