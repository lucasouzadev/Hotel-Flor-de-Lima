// Navigation JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
});

function initializeNavigation() {
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize user menu
    initializeUserMenu();
    
    // Initialize back to top button
    initializeBackToTop();
    
    // Initialize newsletter form
    initializeNewsletterForm();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize active link highlighting
    initializeActiveLinks();
}

// Mobile Menu Functions
function initializeMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    
    if (hamburger && mobileMenu && mobileMenuOverlay) {
        // Toggle mobile menu
        hamburger.addEventListener('click', toggleMobileMenu);
        
        // Close mobile menu when clicking on overlay
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        
        // Close mobile menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('.mobile-nav-link');
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
        
        // Close mobile menu with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
}

function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const hamburger = document.querySelector('.hamburger');
    const body = document.body;
    
    if (mobileMenu && mobileMenuOverlay && hamburger) {
        mobileMenu.classList.toggle('active');
        mobileMenuOverlay.classList.toggle('active');
        hamburger.classList.toggle('active');
        body.classList.toggle('menu-open');
        
        // Prevent body scroll when menu is open
        if (mobileMenu.classList.contains('active')) {
            body.style.overflow = 'hidden';
        } else {
            body.style.overflow = 'auto';
        }
    }
}

function closeMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const hamburger = document.querySelector('.hamburger');
    const body = document.body;
    
    if (mobileMenu && mobileMenuOverlay && hamburger) {
        mobileMenu.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        hamburger.classList.remove('active');
        body.classList.remove('menu-open');
        body.style.overflow = 'auto';
    }
}

// User Menu Functions
function initializeUserMenu() {
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuToggle && userDropdown) {
        // Toggle user menu
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleUserMenu();
        });
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                closeUserMenu();
            }
        });
        
        // Close user menu with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && userDropdown.classList.contains('active')) {
                closeUserMenu();
            }
        });
    }
}

function toggleUserMenu() {
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        userDropdown.classList.toggle('active');
    }
}

function closeUserMenu() {
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        userDropdown.classList.remove('active');
    }
}

// Back to Top Button
function initializeBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        // Smooth scroll to top
        backToTopBtn.addEventListener('click', scrollToTop);
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Newsletter Form
function initializeNewsletterForm() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (!email) {
                showNewsletterMessage('Por favor, insira um email válido.', 'error');
                return;
            }
            
            if (!isValidEmail(email)) {
                showNewsletterMessage('Por favor, insira um email válido.', 'error');
                return;
            }
            
            // Show loading state
            const button = this.querySelector('button');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                // Show success message
                showNewsletterMessage('Obrigado! Você foi inscrito em nossa newsletter.', 'success');
                emailInput.value = '';
            }, 1500);
        });
    }
}

function showNewsletterMessage(message, type) {
    // Remove existing messages
    const existingMessage = document.querySelector('.newsletter-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `newsletter-message newsletter-message--${type}`;
    messageDiv.textContent = message;
    
    // Insert after newsletter form
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.parentNode.insertBefore(messageDiv, newsletterForm.nextSibling);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// Smooth Scrolling
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '#top') {
                e.preventDefault();
                scrollToTop();
                return;
            }
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                
                // Calculate offset for fixed header
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                closeMobileMenu();
            }
        });
    });
}

// Active Link Highlighting
function initializeActiveLinks() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        // Remove existing active class
        link.classList.remove('active');
        
        // Add active class if current page matches
        if (href === currentPage || 
            (currentPage === '' && href === 'index.php') ||
            (currentPage === 'index.php' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
}

// Utility Functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Navbar Scroll Effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.pageYOffset > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Search Functionality (if search bar exists)
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');
    
    if (searchInput && searchResults) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
}

function performSearch(query) {
    // Implement search functionality here
    console.log('Searching for:', query);
    
    // Example: Make AJAX request to search endpoint
    /*
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data.results);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    */
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('.search-results');
    if (!searchResults) return;
    
    if (results.length === 0) {
        searchResults.innerHTML = '<p>Nenhum resultado encontrado.</p>';
    } else {
        searchResults.innerHTML = results.map(result => `
            <a href="${result.url}" class="search-result-item">
                <h4>${result.title}</h4>
                <p>${result.description}</p>
            </a>
        `).join('');
    }
    
    searchResults.style.display = 'block';
}

// Keyboard Navigation
document.addEventListener('keydown', function(e) {
    // Alt + M: Toggle mobile menu
    if (e.altKey && e.key === 'm') {
        e.preventDefault();
        toggleMobileMenu();
    }
    
    // Alt + H: Go to home
    if (e.altKey && e.key === 'h') {
        e.preventDefault();
        window.location.href = 'index.php';
    }
    
    // Alt + U: Go to units
    if (e.altKey && e.key === 'u') {
        e.preventDefault();
        window.location.href = 'units.php';
    }
    
    // Alt + R: Go to reservations
    if (e.altKey && e.key === 'r') {
        e.preventDefault();
        window.location.href = 'reservations.php';
    }
});

// Export functions for global access
window.navigationUtils = {
    toggleMobileMenu,
    closeMobileMenu,
    toggleUserMenu,
    closeUserMenu,
    scrollToTop,
    showNewsletterMessage,
    isValidEmail
};
