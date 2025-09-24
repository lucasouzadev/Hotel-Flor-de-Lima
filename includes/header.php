<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $isLoggedIn ? $_SESSION : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Hotel Flor de Lima</title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Hotel Flor de Lima - Uma experiência única de hospedagem e gastronomia, onde tradições eslavas e japonesas se encontram.'; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($additionalCSS) && is_array($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Hotel Flor de Lima">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Hotel Flor de Lima">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Uma experiência única de hospedagem e gastronomia.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/assets/images/og-image.jpg">
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <!-- Logo -->
                <div class="nav-logo">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="Hotel Flor de Lima" class="logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <h2 class="logo-text">Hotel Flor de Lima</h2>
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Início</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="units.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'units.php' ? 'active' : ''; ?>">
                            <i class="fas fa-bed"></i>
                            <span>Unidades</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="reservations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check"></i>
                            <span>Reservas</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="accommodation.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'accommodation.php' ? 'active' : ''; ?>">
                            <i class="fas fa-swimming-pool"></i>
                            <span>Hospedagens</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="bar-celina.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bar-celina.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cocktail"></i>
                            <span>Bar Celina</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="newspaper.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'newspaper.php' ? 'active' : ''; ?>">
                            <i class="fas fa-newspaper"></i>
                            <span>O CORVO</span>
                        </a>
                    </li>
                    
                    <!-- User Menu -->
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item nav-item--user">
                            <div class="user-menu">
                                <button class="user-menu-toggle" onclick="toggleUserMenu()">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo htmlspecialchars($currentUser['name'] ?? 'Usuário'); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="user-dropdown" id="userDropdown">
                                    <a href="dashboard.php" class="dropdown-item">
                                        <i class="fas fa-tachometer-alt"></i>
                                        Dashboard
                                    </a>
                                    <a href="dashboard.php#profile" class="dropdown-item">
                                        <i class="fas fa-user-edit"></i>
                                        Meu Perfil
                                    </a>
                                    <a href="dashboard.php#reservations" class="dropdown-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        Minhas Reservas
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item dropdown-item--danger">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Sair
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link nav-link--login">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php" class="nav-link nav-link--register">
                                <i class="fas fa-user-plus"></i>
                                <span>Registro</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Mobile Menu Toggle -->
                <div class="hamburger" onclick="toggleMobileMenu()">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <h3>Menu</h3>
            <button class="mobile-menu-close" onclick="closeMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mobile-menu-content">
            <a href="index.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Início</span>
            </a>
            
            <a href="units.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'units.php' ? 'active' : ''; ?>">
                <i class="fas fa-bed"></i>
                <span>Unidades</span>
            </a>
            
            <a href="reservations.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Reservas</span>
            </a>
            
            <a href="accommodation.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'accommodation.php' ? 'active' : ''; ?>">
                <i class="fas fa-swimming-pool"></i>
                <span>Hospedagens</span>
            </a>
            
            <a href="bar-celina.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bar-celina.php' ? 'active' : ''; ?>">
                <i class="fas fa-cocktail"></i>
                <span>Bar Celina</span>
            </a>
            
            <a href="newspaper.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'newspaper.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i>
                <span>O CORVO</span>
            </a>
            
            <?php if ($isLoggedIn): ?>
                <div class="mobile-user-section">
                    <div class="mobile-user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($currentUser['name'] ?? 'Usuário'); ?></span>
                    </div>
                    
                    <a href="dashboard.php" class="mobile-nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="dashboard.php#profile" class="mobile-nav-link">
                        <i class="fas fa-user-edit"></i>
                        <span>Meu Perfil</span>
                    </a>
                    
                    <a href="dashboard.php#reservations" class="mobile-nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Minhas Reservas</span>
                    </a>
                    
                    <a href="logout.php" class="mobile-nav-link mobile-nav-link--danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            <?php else: ?>
                <a href="login.php" class="mobile-nav-link">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                
                <a href="register.php" class="mobile-nav-link">
                    <i class="fas fa-user-plus"></i>
                    <span>Registro</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="main-content"><?php // Content will be inserted here ?>
