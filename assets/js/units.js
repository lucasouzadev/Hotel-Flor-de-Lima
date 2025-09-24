// Units and Rooms JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeUnitsSystem();
});

function initializeUnitsSystem() {
    // Set minimum dates for search inputs
    setMinimumDates();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize search functionality
    initializeSearchFunctionality();
    
    // Initialize cart functionality
    initializeCartFunctionality();
    
    // Initialize responsive features
    initializeResponsiveFeatures();
    
    // Initialize animations
    initializeAnimations();
}

// Set minimum dates for search inputs
function setMinimumDates() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput) {
        const today = new Date().toISOString().split('T')[0];
        checkInInput.min = today;
        checkInInput.value = today;
    }
    
    if (checkOutInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowString = tomorrow.toISOString().split('T')[0];
        checkOutInput.min = tomorrowString;
        checkOutInput.value = tomorrowString;
    }
}

// Initialize form validation
function initializeFormValidation() {
    const form = document.getElementById('availabilitySearchForm');
    if (!form) return;
    
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const guestsInput = document.getElementById('guests');
    
    // Date validation
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            validateDateRange();
            updateCheckOutMinimum();
        });
        
        checkOutInput.addEventListener('change', function() {
            validateDateRange();
        });
    }
    
    // Guests validation
    if (guestsInput) {
        guestsInput.addEventListener('change', function() {
            validateGuests(this);
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!validateSearchForm()) {
            e.preventDefault();
            showMessage('Por favor, corrija os erros no formulário.', 'error');
        }
    });
}

// Update check-out minimum date based on check-in
function updateCheckOutMinimum() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput && checkOutInput && checkInInput.value) {
        const checkInDate = new Date(checkInInput.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        const minCheckOut = checkInDate.toISOString().split('T')[0];
        
        checkOutInput.min = minCheckOut;
        
        // If current check-out is before new minimum, update it
        if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
            checkOutInput.value = minCheckOut;
        }
    }
}

// Validate date range
function validateDateRange() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (!checkInInput || !checkOutInput) return false;
    
    const checkIn = checkInInput.value;
    const checkOut = checkOutInput.value;
    
    const checkInErrorElement = getOrCreateErrorElement(checkInInput);
    const checkOutErrorElement = getOrCreateErrorElement(checkOutInput);
    
    if (!checkIn || !checkOut) {
        hideFieldError(checkInErrorElement);
        hideFieldError(checkOutErrorElement);
        return false;
    }
    
    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    let isValid = true;
    
    // Check if check-in is in the past
    if (checkInDate < today) {
        showFieldError(checkInErrorElement, 'A data de check-in não pode ser no passado.');
        isValid = false;
    } else {
        hideFieldError(checkInErrorElement);
    }
    
    // Check if check-out is after check-in
    if (checkOutDate <= checkInDate) {
        showFieldError(checkOutErrorElement, 'A data de check-out deve ser posterior à data de check-in.');
        isValid = false;
    } else {
        hideFieldError(checkOutErrorElement);
    }
    
    return isValid;
}

// Validate guests input
function validateGuests(guestsInput) {
    const guests = parseInt(guestsInput.value);
    
    const errorElement = getOrCreateErrorElement(guestsInput);
    
    if (guests < 1 || guests > 8) {
        showFieldError(errorElement, 'Número de hóspedes deve ser entre 1 e 8.');
        return false;
    } else {
        hideFieldError(errorElement);
        return true;
    }
}

// Validate entire search form
function validateSearchForm() {
    let isValid = true;
    
    // Validate date range
    if (!validateDateRange()) {
        isValid = false;
    }
    
    // Validate guests
    const guestsInput = document.getElementById('guests');
    if (guestsInput && !validateGuests(guestsInput)) {
        isValid = false;
    }
    
    return isValid;
}

// Initialize search functionality
function initializeSearchFunctionality() {
    // Auto-submit form when all required fields are filled
    const form = document.getElementById('availabilitySearchForm');
    if (form) {
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                // Check if all required fields are filled
                const allFilled = Array.from(inputs).every(input => input.value.trim() !== '');
                
                if (allFilled && validateSearchForm()) {
                    // Optional: Auto-submit after a short delay
                    // setTimeout(() => form.submit(), 1000);
                }
            });
        });
    }
}

// Initialize cart functionality
function initializeCartFunctionality() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('[onclick*="addToCart"]');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Extract parameters from onclick attribute
            const onclickAttr = this.getAttribute('onclick');
            const match = onclickAttr.match(/addToCart\('([^']+)',\s*(\d+),\s*([\d.]+)\)/);
            
            if (match) {
                const itemType = match[1];
                const itemId = parseInt(match[2]);
                const price = parseFloat(match[3]);
                
                addToCart(itemType, itemId, price);
            }
        });
    });
}

// Add item to cart
function addToCart(itemType, itemId, price) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Adicionando...';
    button.disabled = true;
    
    // Make AJAX request to add to cart
    fetch('api/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            item_type: itemType,
            item_id: itemId,
            price: price,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Item adicionado ao carrinho com sucesso!', 'success');
            
            // Update cart counter if exists
            updateCartCounter();
            
            // Optional: Show cart preview
            showCartPreview();
        } else {
            showMessage(data.message || 'Erro ao adicionar item ao carrinho.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Erro de conexão. Tente novamente.', 'error');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Update cart counter
function updateCartCounter() {
    const cartCounter = document.querySelector('.cart-counter');
    if (cartCounter) {
        // Make request to get cart count
        fetch('api/get-cart-count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartCounter.textContent = data.count;
                    cartCounter.style.display = data.count > 0 ? 'block' : 'none';
                }
            })
            .catch(error => console.error('Error updating cart counter:', error));
    }
}

// Show cart preview
function showCartPreview() {
    // Create or update cart preview modal
    let cartPreview = document.getElementById('cartPreview');
    
    if (!cartPreview) {
        cartPreview = document.createElement('div');
        cartPreview.id = 'cartPreview';
        cartPreview.className = 'cart-preview-modal';
        cartPreview.innerHTML = `
            <div class="cart-preview-content">
                <div class="cart-preview-header">
                    <h3>Carrinho de Compras</h3>
                    <span class="close-cart" onclick="closeCartPreview()">&times;</span>
                </div>
                <div class="cart-preview-body">
                    <div class="cart-items"></div>
                </div>
                <div class="cart-preview-footer">
                    <a href="checkout.php" class="btn btn-primary">Finalizar Compra</a>
                </div>
            </div>
        `;
        document.body.appendChild(cartPreview);
    }
    
    // Load cart items
    loadCartItems();
    
    // Show modal
    cartPreview.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        closeCartPreview();
    }, 3000);
}

// Load cart items
function loadCartItems() {
    const cartItemsContainer = document.querySelector('.cart-items');
    if (!cartItemsContainer) return;
    
    fetch('api/get-cart-items.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.items.length === 0) {
                    cartItemsContainer.innerHTML = '<p>Carrinho vazio</p>';
                } else {
                    cartItemsContainer.innerHTML = data.items.map(item => `
                        <div class="cart-item">
                            <span>${item.name}</span>
                            <span>R$ ${item.price.toFixed(2)}</span>
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => console.error('Error loading cart items:', error));
}

// Close cart preview
function closeCartPreview() {
    const cartPreview = document.getElementById('cartPreview');
    if (cartPreview) {
        cartPreview.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Cancel reservation
function cancelReservation(reservationId) {
    if (confirm('Tem certeza que deseja cancelar esta reserva?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.innerHTML = '<span class="loading"></span> Cancelando...';
        button.disabled = true;
        
        // Make AJAX request to cancel reservation
        fetch('api/cancel-reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reservation_id: reservationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Reserva cancelada com sucesso!', 'success');
                // Reload page to update the list
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showMessage(data.message || 'Erro ao cancelar reserva.', 'error');
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Erro de conexão. Tente novamente.', 'error');
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Scroll to search section
function scrollToSearch() {
    const searchSection = document.querySelector('.search-section');
    if (searchSection) {
        searchSection.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Focus on first input after scroll
        setTimeout(() => {
            const firstInput = searchSection.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }
        }, 500);
    }
}

// Initialize responsive features
function initializeResponsiveFeatures() {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
    
    // Lazy loading for images
    initializeLazyLoading();
    
    // Smooth scrolling for anchor links
    initializeSmoothScrolling();
}

// Initialize animations
function initializeAnimations() {
    // Animate cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe all cards
    const cards = document.querySelectorAll('.room-type-card, .room-card, .promotion-card, .reservation-card');
    cards.forEach(card => {
        observer.observe(card);
    });
}

// Initialize lazy loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
}

// Initialize smooth scrolling
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Show message
function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    
    const iconClass = type === 'error' ? 'exclamation-triangle' : 
                     type === 'success' ? 'check-circle' : 'info-circle';
    messageDiv.innerHTML = `
        <i class="fas fa-${iconClass}"></i>
        ${message}
    `;
    
    // Insert at the top of main content
    const main = document.querySelector('.units-main .container');
    if (main) {
        main.insertBefore(messageDiv, main.firstChild);
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Helper function to get or create error element
function getOrCreateErrorElement(input) {
    let errorElement = input.parentNode.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        `;
        input.parentNode.appendChild(errorElement);
    }
    return errorElement;
}

// Show field error
function showFieldError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

// Hide field error
function hideFieldError(errorElement) {
    errorElement.style.display = 'none';
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(amount);
}

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Utility function to calculate nights
function calculateNights(checkIn, checkOut) {
    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);
    const diffTime = Math.abs(checkOutDate - checkInDate);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

// Utility function to check if date is today
function isToday(dateString) {
    const today = new Date();
    const date = new Date(dateString);
    return date.toDateString() === today.toDateString();
}

// Utility function to check if date is in the past
function isPastDate(dateString) {
    const today = new Date();
    const date = new Date(dateString);
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    return date < today;
}

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for performance
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Export functions for global access
window.unitsUtils = {
    addToCart,
    cancelReservation,
    scrollToSearch,
    showMessage,
    formatCurrency,
    formatDate,
    calculateNights,
    isToday,
    isPastDate
};
