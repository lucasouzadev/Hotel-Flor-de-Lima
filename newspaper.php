<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$db = new Database();
$message = '';
$messageType = '';

// Buscar categorias do jornal
$categories = $db->fetchAll("SELECT * FROM newspaper_categories ORDER BY name");

// Buscar artigos publicados
$articles = $db->fetchAll(
    "SELECT a.*, c.name as category_name, u.name as author_name
     FROM newspaper_articles a
     JOIN newspaper_categories c ON a.category_id = c.id
     JOIN users u ON a.author_id = u.id
     WHERE a.status = 'published'
     ORDER BY a.published_at DESC, a.created_at DESC
     LIMIT 10"
);

// Buscar comentários aprovados (incluindo comentários gerais)
$comments = $db->fetchAll(
    "SELECT c.*, u.name as author_name, a.title as article_title
     FROM comments c
     JOIN users u ON c.user_id = u.id
     LEFT JOIN newspaper_articles a ON c.article_id = a.id
     WHERE c.status = 'approved'
     ORDER BY c.created_at DESC
     LIMIT 10"
);

// Processar comentário
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    if (!$auth->isLoggedIn()) {
        $message = 'Você precisa fazer login para comentar.';
        $messageType = 'error';
    } else {
        $content = trim($_POST['content'] ?? '');
        $articleId = (int)($_POST['article_id'] ?? 0);
        
        if (empty($content)) {
            $message = 'Por favor, escreva um comentário.';
            $messageType = 'error';
        } elseif (strlen($content) < 10) {
            $message = 'O comentário deve ter pelo menos 10 caracteres.';
            $messageType = 'error';
        } else {
            try {
                $db->insert('comments', [
                    'article_id' => $articleId ?: null,
                    'user_id' => $auth->getCurrentUser()['id'],
                    'content' => $content,
                    'status' => 'pending'
                ]);
                
                $message = 'Comentário enviado! Aguarde aprovação.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Erro ao enviar comentário: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Processar feedback
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_feedback') {
    if (!$auth->isLoggedIn()) {
        $message = 'Você precisa fazer login para enviar feedback.';
        $messageType = 'error';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = $_POST['category'] ?? 'general';
        
        if ($rating < 1 || $rating > 5) {
            $message = 'Por favor, selecione uma avaliação de 1 a 5 estrelas.';
            $messageType = 'error';
        } elseif (empty($content) || strlen($content) < 10) {
            $message = 'Por favor, escreva um feedback com pelo menos 10 caracteres.';
            $messageType = 'error';
        } else {
            try {
                $db->insert('feedbacks', [
                    'user_id' => $auth->getCurrentUser()['id'],
                    'rating' => $rating,
                    'title' => $title,
                    'content' => $content,
                    'category' => $category,
                    'status' => 'pending'
                ]);
                
                $message = 'Feedback enviado! Obrigado pela sua avaliação.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Erro ao enviar feedback: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O CORVO - Jornal do Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/newspaper.css">
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
                    <li><a href="newspaper.php" class="active">O CORVO</a></li>
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
    <section class="newspaper-hero">
        <div class="container">
            <div class="hero-content">
                <h1><i class="fas fa-newspaper"></i> O CORVO</h1>
                <p>Jornal Oficial do Hotel Flor de Lima</p>
                <span class="newspaper-subtitle">Notícias • Feedback • Comunidade</span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="newspaper-main">
        <div class="container">
            <?php if ($message): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="newspaper-layout">
                <!-- Articles Section -->
                <section class="articles-section">
                    <h2><i class="fas fa-newspaper"></i> Últimas Notícias</h2>
                    
                    <?php if (empty($articles)): ?>
                        <div class="no-content">
                            <i class="fas fa-newspaper"></i>
                            <h3>Nenhuma notícia disponível</h3>
                            <p>Em breve teremos novidades para compartilhar!</p>
                        </div>
                    <?php else: ?>
                        <div class="articles-grid">
                            <?php foreach ($articles as $article): ?>
                                <article class="article-card">
                                    <div class="article-image">
                                        <img src="assets/images/articles/<?php echo $article['featured_image'] ?? 'default-article.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($article['title']); ?>"
                                             onerror="this.src='assets/images/articles/default-article.jpg'">
                                        <div class="article-category">
                                            <?php echo htmlspecialchars($article['category_name']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="article-content">
                                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($article['content'], 0, 150)) . '...'; ?></p>
                                        
                                        <div class="article-meta">
                                            <div class="article-author">
                                                <i class="fas fa-user"></i>
                                                <span><?php echo htmlspecialchars($article['author_name']); ?></span>
                                            </div>
                                            <div class="article-date">
                                                <i class="fas fa-calendar"></i>
                                                <span><?php echo date('d/m/Y', strtotime($article['published_at'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary btn-small">
                                            Ler mais
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Sidebar -->
                <aside class="newspaper-sidebar">
                    <!-- Feedback Section -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-star"></i> Deixe seu Feedback</h3>
                        <div class="feedback-form">
                            <?php if ($auth->isLoggedIn()): ?>
                                <form method="POST" class="feedback-form-content">
                                    <input type="hidden" name="action" value="add_feedback">
                                    
                                    <div class="rating-input">
                                        <label>Avaliação:</label>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                                <label for="star<?php echo $i; ?>" class="star">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <input type="text" name="title" placeholder="Título do feedback (opcional)" maxlength="200">
                                    </div>
                                    
                                    <div class="form-group">
                                        <select name="category" required>
                                            <option value="">Categoria</option>
                                            <option value="accommodation">Hospedagem</option>
                                            <option value="service">Atendimento</option>
                                            <option value="food">Gastronomia</option>
                                            <option value="facilities">Instalações</option>
                                            <option value="general">Geral</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <textarea name="content" rows="4" placeholder="Seu feedback..." required minlength="10"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-full">
                                        <i class="fas fa-paper-plane"></i>
                                        Enviar
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="login-prompt">
                                    <p>Faça login para enviar feedback</p>
                                    <a href="login.php" class="btn btn-primary">Fazer Login</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Feedbacks Section -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-star"></i> Feedbacks Recentes</h3>
                        <div class="feedbacks-list">
                            <?php 
                            // Buscar feedbacks aprovados
                            $recentFeedbacks = $db->fetchAll(
                                "SELECT f.*, u.name as author_name
                                 FROM feedbacks f
                                 JOIN users u ON f.user_id = u.id
                                 WHERE f.status = 'approved'
                                 ORDER BY f.created_at DESC
                                 LIMIT 5"
                            );
                            ?>
                            
                            <?php if (empty($recentFeedbacks)): ?>
                                <div class="no-feedbacks">
                                    <i class="fas fa-star"></i>
                                    <p>Nenhum feedback ainda</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentFeedbacks as $feedback): ?>
                                    <div class="feedback-item">
                                        <div class="feedback-header">
                                            <div class="feedback-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'filled' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small><?php echo date('d/m/Y', strtotime($feedback['created_at'])); ?></small>
                                        </div>
                                        
                                        <?php if ($feedback['title']): ?>
                                            <div class="feedback-title">
                                                <strong><?php echo htmlspecialchars($feedback['title']); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="feedback-content">
                                            <?php echo htmlspecialchars($feedback['content']); ?>
                                        </div>
                                        
                                        <div class="feedback-author">
                                            <small>- <?php echo htmlspecialchars($feedback['author_name']); ?></small>
                                        </div>
                                        
                                        <div class="feedback-category">
                                            <span class="category-badge category-<?php echo $feedback['category']; ?>">
                                                <?php 
                                                $categoryLabels = [
                                                    'accommodation' => 'Hospedagem',
                                                    'service' => 'Atendimento',
                                                    'food' => 'Gastronomia',
                                                    'facilities' => 'Instalações',
                                                    'general' => 'Geral'
                                                ];
                                                echo $categoryLabels[$feedback['category']] ?? ucfirst($feedback['category']);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Comments Section -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-comments"></i> Comentários Recentes</h3>
                        <div class="comments-list">
                            <?php if (empty($comments)): ?>
                                <div class="no-comments">
                                    <i class="fas fa-comment-slash"></i>
                                    <p>Nenhum comentário ainda</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="comment-header">
                                            <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                                            <small><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo htmlspecialchars($comment['content']); ?>
                                        </div>
                                        <?php if ($comment['article_title']): ?>
                                            <div class="comment-article">
                                                <small>Em: <?php echo htmlspecialchars($comment['article_title']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- General Comment Section -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-comment"></i> Comentário Geral</h3>
                        <div class="general-comment-form">
                            <?php if ($auth->isLoggedIn()): ?>
                                <form method="POST" class="comment-form">
                                    <input type="hidden" name="action" value="add_comment">
                                    
                                    <div class="form-group">
                                        <textarea name="content" rows="3" placeholder="Deixe seu comentário sobre o hotel..." required minlength="10"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-secondary btn-full">
                                        <i class="fas fa-comment"></i>
                                        Comentar
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="login-prompt">
                                    <p>Faça login para comentar</p>
                                    <a href="login.php" class="btn btn-secondary">Fazer Login</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-tags"></i> Categorias</h3>
                        <div class="categories-list">
                            <?php foreach ($categories as $category): ?>
                                <a href="newspaper.php?category=<?php echo urlencode($category['name']); ?>" class="category-link">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="sidebar-section">
                        <h3><i class="fas fa-chart-bar"></i> Estatísticas</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($articles); ?></div>
                                <div class="stat-label">Artigos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($recentFeedbacks); ?></div>
                                <div class="stat-label">Feedbacks</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($comments); ?></div>
                                <div class="stat-label">Comentários</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($categories); ?></div>
                                <div class="stat-label">Categorias</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

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
    <script src="assets/js/newspaper.js"></script>
</body>
</html>
