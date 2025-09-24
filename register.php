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

// Processar registro
if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $document = trim($_POST['document'] ?? '');
    
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'As senhas não coincidem.';
        $messageType = 'error';
    } else {
        $result = $auth->register($name, $email, $password, $phone, $birth_date, $document);
        
        if ($result['success']) {
            $message = 'Conta criada com sucesso! Faça login para continuar.';
            $messageType = 'success';
            
            // Limpar campos do formulário
            $_POST = [];
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
    <title>Cadastro - Hotel Flor de Lima</title>
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
                <h2>Criar nova conta</h2>
                <p>Cadastre-se para acessar nossos serviços exclusivos</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i>
                        Nome completo *
                    </label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email *
                    </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Senha *
                        </label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirmar senha *
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i>
                        Telefone
                    </label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="(XX) XXXXX-XXXX">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="birth_date">
                            <i class="fas fa-calendar"></i>
                            Data de nascimento
                        </label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="document">
                            <i class="fas fa-id-card"></i>
                            CPF
                        </label>
                        <input type="text" id="document" name="document" value="<?php echo htmlspecialchars($_POST['document'] ?? ''); ?>" placeholder="XXX.XXX.XXX-XX">
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        Aceito os <a href="terms.php" target="_blank">termos de uso</a> e <a href="privacy.php" target="_blank">política de privacidade</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Criar conta
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao início
                </a>
            </div>
        </div>
        
        <div class="auth-image">
            <img src="assets/images/hotel-register.jpg" alt="Hotel Flor de Lima">
            <div class="auth-overlay">
                <h3>Junte-se a nós!</h3>
                <p>Faça parte da família Flor de Lima e desfrute de experiências únicas.</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
