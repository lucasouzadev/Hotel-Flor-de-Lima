<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Flor de Lima - Hospedagem e Gastronomia</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li><a href="#about">Sobre</a></li>
                    <li><a href="units.php">Unidades</a></li>
                    <li><a href="reservations.php">Reservas</a></li>
                    <li><a href="accommodation.php">Hospedagens</a></li>
                    <li><a href="bar-celina.php">Bar Celina</a></li>
                    <li><a href="newspaper.php">O CORVO</a></li>
                    <li><a href="#contact">Contato</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Conectar-se</a></li>
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
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Bem-vindo ao Hotel Flor de Lima</h1>
            <p>Uma experiência única de hospedagem e gastronomia</p>
            <div class="hero-buttons">
                <a href="reservations.php" class="btn btn-primary">Fazer Reserva</a>
                <a href="#about" class="btn btn-secondary">Conhecer Mais</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hotel-main.jpg" alt="Hotel Flor de Lima">
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>Sobre o Hotel Flor de Lima</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>O Hotel Flor de Lima é mais que um local de hospedagem - é uma experiência completa que combina conforto, gastronomia excepcional e um ambiente único inspirado nas culturas eslava e japonesa.</p>
                    <p>Localizado em um ambiente privilegiado, oferecemos quartos elegantes, espaços de lazer de primeira qualidade e o renomado Bar Celina, onde você pode desfrutar de drinks únicos e uma atmosfera inesquecível.</p>
                </div>
                <div class="about-features">
                    <div class="feature">
                        <i class="fas fa-bed"></i>
                        <h3>Quartos Elegantes</h3>
                        <p>Conforto e sofisticação em cada detalhe</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-utensils"></i>
                        <h3>Gastronomia Única</h3>
                        <p>Sabores que misturam tradições eslavas e japonesas</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-cocktail"></i>
                        <h3>Bar Celina</h3>
                        <p>Drinks especiais em ambiente exclusivo</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="rooms">
        <div class="container">
            <h2>Nossos Quartos</h2>
            <div class="rooms-grid">
                <div class="room-card">
                    <img src="assets/images/room-standard.jpg" alt="Quarto Standard">
                    <div class="room-info">
                        <h3>Quarto Standard</h3>
                        <p>Conforto essencial para uma estadia agradável</p>
                        <ul>
                            <li>Cama de casal ou solteiro</li>
                            <li>Wi-Fi gratuito</li>
                            <li>TV Smart</li>
                            <li>Banheiro privativo</li>
                        </ul>
                        <div class="room-price">A partir de R$ 180/noite</div>
                        <a href="reservations.php?room=standard" class="btn btn-primary">Reservar</a>
                    </div>
                </div>
                <div class="room-card">
                    <img src="assets/images/room-deluxe.jpg" alt="Quarto Deluxe">
                    <div class="room-info">
                        <h3>Quarto Deluxe</h3>
                        <p>Luxo e elegância em ambiente espaçoso</p>
                        <ul>
                            <li>Cama king size</li>
                            <li>Área de estar</li>
                            <li>Vista privilegiada</li>
                            <li>Mini bar</li>
                        </ul>
                        <div class="room-price">A partir de R$ 320/noite</div>
                        <a href="reservations.php?room=deluxe" class="btn btn-primary">Reservar</a>
                    </div>
                </div>
                <div class="room-card">
                    <img src="assets/images/room-suite.jpg" alt="Suíte Presidencial">
                    <div class="room-info">
                        <h3>Suíte Presidencial</h3>
                        <p>O máximo em conforto e sofisticação</p>
                        <ul>
                            <li>Suíte completa</li>
                            <li>Salão privativo</li>
                            <li>Serviço de concierge</li>
                            <li>Vista panorâmica</li>
                        </ul>
                        <div class="room-price">A partir de R$ 580/noite</div>
                        <a href="reservations.php?room=suite" class="btn btn-primary">Reservar</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities" class="amenities">
        <div class="container">
            <h2>Comodidades e Segurança</h2>
            <div class="amenities-grid">
                <div class="amenity">
                    <i class="fas fa-swimming-pool"></i>
                    <h3>Piscina</h3>
                    <p>Área de lazer com piscina aquecida e vista panorâmica</p>
                </div>
                <div class="amenity">
                    <i class="fas fa-spa"></i>
                    <h3>Spa & Wellness</h3>
                    <p>Tratamentos relaxantes e massagens terapêuticas</p>
                </div>
                <div class="amenity">
                    <i class="fas fa-dumbbell"></i>
                    <h3>Academia</h3>
                    <p>Equipamentos modernos para manter sua rotina fitness</p>
                </div>
                <div class="amenity">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Segurança 24h</h3>
                    <p>Monitoramento constante para sua tranquilidade</p>
                </div>
                <div class="amenity">
                    <i class="fas fa-wifi"></i>
                    <h3>Wi-Fi Premium</h3>
                    <p>Internet de alta velocidade em todo o hotel</p>
                </div>
                <div class="amenity">
                    <i class="fas fa-car"></i>
                    <h3>Estacionamento</h3>
                    <p>Vagas seguras e cobertas para hóspedes</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bar Celina Section -->
    <section id="bar" class="bar-section">
        <div class="container">
            <h2>Bar Celina</h2>
            <div class="bar-content">
                <div class="bar-info">
                    <h3>Uma Experiência Única</h3>
                    <p>O Bar Celina é o coração gastronômico do Hotel Flor de Lima. Nossa carta de drinks é inspirada nas tradições eslavas e japonesas, oferecendo uma experiência sensorial única.</p>
                    <div class="bar-features">
                        <div class="bar-feature">
                            <i class="fas fa-cocktail"></i>
                            <span>Drinks Especiais</span>
                        </div>
                        <div class="bar-feature">
                            <i class="fas fa-leaf"></i>
                            <span>Opções Sem Álcool</span>
                        </div>
                        <div class="bar-feature">
                            <i class="fas fa-child"></i>
                            <span>Menu Infantil</span>
                        </div>
                    </div>
                    <a href="bar-celina.php" class="btn btn-primary">Ver Carta Completa</a>
                </div>
                <div class="bar-image">
                    <img src="assets/images/bar-celina.jpg" alt="Bar Celina">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2>O que nossos hóspedes dizem</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Uma experiência incrível! O Bar Celina tem drinks únicos e a atmosfera é perfeita para relaxar."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/images/guest1.jpg" alt="Maria Silva">
                        <div class="author-info">
                            <h4>Maria Silva</h4>
                            <span>Influencer de Viagem</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Hotel impecável! Quartos confortáveis, atendimento excepcional e comodidades de primeira."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/images/guest2.jpg" alt="João Santos">
                        <div class="author-info">
                            <h4>João Santos</h4>
                            <span>Hóspede Frequente</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"A mistura de culturas no Bar Celina é fascinante. Drinks criativos e ambiente acolhedor."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/images/guest3.jpg" alt="Ana Costa">
                        <div class="author-info">
                            <h4>Ana Costa</h4>
                            <span>Crítica Gastronômica</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newspaper Preview -->
    <section id="newspaper" class="newspaper-preview">
        <div class="container">
            <h2>O CORVO - Jornal do Hotel</h2>
            <div class="newspaper-content">
                <div class="newspaper-info">
                    <h3>Fique por dentro das novidades</h3>
                    <p>O CORVO é nosso jornal interno onde você encontra notícias sobre o hotel, feedbacks de hóspedes, dicas de viagem e muito mais.</p>
                    <ul>
                        <li>Notícias e atualizações do hotel</li>
                        <li>Feedbacks e avaliações de hóspedes</li>
                        <li>Fórum para interação entre hóspedes</li>
                        <li>Dicas de gastronomia e turismo</li>
                    </ul>
                    <a href="newspaper.php" class="btn btn-primary">Acessar O CORVO</a>
                </div>
                <div class="newspaper-highlights">
                    <h4>Destaques desta semana:</h4>
                    <div class="highlight">
                        <h5>Novos Drinks no Bar Celina</h5>
                        <p>Conheça nossa nova seleção de drinks inspirados na primavera japonesa.</p>
                    </div>
                    <div class="highlight">
                        <h5>Promoção Especial</h5>
                        <p>Desconto de 20% em estadias de 3 ou mais noites.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Entre em Contato</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Endereço</h4>
                            <p>Rua das Flores, 123<br>Centro - Lima, PE</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Telefone</h4>
                            <p>(81) 3456-7890</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>contato@hotelflordeLima.com.br</p>
                        </div>
                    </div>
                </div>
                <form class="contact-form">
                    <div class="form-group">
                        <input type="text" placeholder="Seu nome" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Seu email" required>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Sua mensagem" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                </form>
            </div>
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
                        <li><a href="#home">Início</a></li>
                        <li><a href="#rooms">Quartos</a></li>
                        <li><a href="#bar">Bar Celina</a></li>
                        <li><a href="#newspaper">O CORVO</a></li>
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
</body>
</html>
