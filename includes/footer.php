    </main> <!-- End main-content -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Hotel Info -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <h3>Hotel Flor de Lima</h3>
                        <p>Uma experiência única de hospedagem e gastronomia, onde tradições eslavas e japonesas se encontram.</p>
                    </div>
                    
                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Endereço</strong>
                                <p>Rua das Flores, 123<br>Centro - Lima, PE</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telefone</strong>
                                <p>(81) 3456-7890</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <p>contato@hotelflordeLima.com.br</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h4>Links Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home"></i> Início</a></li>
                        <li><a href="units.php"><i class="fas fa-bed"></i> Unidades</a></li>
                        <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> Reservas</a></li>
                        <li><a href="accommodation.php"><i class="fas fa-swimming-pool"></i> Hospedagens</a></li>
                        <li><a href="bar-celina.php"><i class="fas fa-cocktail"></i> Bar Celina</a></li>
                        <li><a href="newspaper.php"><i class="fas fa-newspaper"></i> O CORVO</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="footer-section">
                    <h4>Nossos Serviços</h4>
                    <ul class="footer-links">
                        <li><a href="units.php#standard"><i class="fas fa-star"></i> Quartos Standard</a></li>
                        <li><a href="units.php#deluxe"><i class="fas fa-crown"></i> Quartos Deluxe</a></li>
                        <li><a href="units.php#suite"><i class="fas fa-gem"></i> Suite Presidencial</a></li>
                        <li><a href="accommodation.php#spa"><i class="fas fa-spa"></i> Spa & Wellness</a></li>
                        <li><a href="accommodation.php#pool"><i class="fas fa-swimming-pool"></i> Piscina</a></li>
                        <li><a href="accommodation.php#gym"><i class="fas fa-dumbbell"></i> Academia</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter & Social -->
                <div class="footer-section">
                    <h4>Fique Conectado</h4>
                    
                    <!-- Newsletter -->
                    <div class="newsletter">
                        <p>Receba nossas ofertas especiais:</p>
                        <form class="newsletter-form" id="newsletterForm">
                            <div class="newsletter-input">
                                <input type="email" placeholder="Seu email" required>
                                <button type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="social-links">
                        <h5>Redes Sociais</h5>
                        <div class="social-icons">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="social-link" title="TripAdvisor">
                                <i class="fab fa-tripadvisor"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="payment-methods">
                        <h5>Formas de Pagamento</h5>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fas fa-credit-card" title="Cartão de Crédito"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="footer-copyright">
                        <p>&copy; <?php echo date('Y'); ?> Hotel Flor de Lima. Todos os direitos reservados.</p>
                    </div>
                    
                    <div class="footer-legal">
                        <a href="#privacy">Política de Privacidade</a>
                        <span class="separator">|</span>
                        <a href="#terms">Termos de Uso</a>
                        <span class="separator">|</span>
                        <a href="#cookies">Política de Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    <script src="assets/js/navigation.js"></script>
    <script src="assets/js/loading.js"></script>
    
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($pageJS)): ?>
        <script>
            <?php echo $pageJS; ?>
        </script>
    <?php endif; ?>
    
    <!-- Google Analytics (Optional) -->
    <?php if (defined('GA_TRACKING_ID') && GA_TRACKING_ID): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA_TRACKING_ID; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo GA_TRACKING_ID; ?>');
    </script>
    <?php endif; ?>
    
</body>
</html>
