// Accommodation and Leisure Areas JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeAccommodationSystem();
});

function initializeAccommodationSystem() {
    // Set minimum date to today for reservation date input
    setMinimumDate();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize time validation
    initializeTimeValidation();
    
    // Initialize modal functionality
    initializeModal();
    
    // Initialize responsive features
    initializeResponsiveFeatures();
}

// Set minimum date to today
function setMinimumDate() {
    const dateInput = document.getElementById('reservation_date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
    }
}

// Initialize form validation
function initializeFormValidation() {
    const form = document.getElementById('leisureReservationForm');
    if (!form) return;
    
    const dateInput = document.getElementById('reservation_date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const guestsInput = document.getElementById('guests');
    
    // Date validation
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            validateDate(this);
            if (startTimeInput && endTimeInput) {
                validateTimeRange();
            }
        });
    }
    
    // Time validation
    if (startTimeInput && endTimeInput) {
        startTimeInput.addEventListener('change', validateTimeRange);
        endTimeInput.addEventListener('change', validateTimeRange);
    }
    
    // Guests validation
    if (guestsInput) {
        guestsInput.addEventListener('input', function() {
            validateGuests(this);
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            showMessage('Por favor, corrija os erros no formulário.', 'error');
        }
    });
}

// Validate date input
function validateDate(dateInput) {
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const errorElement = getOrCreateErrorElement(dateInput);
    
    if (selectedDate < today) {
        showFieldError(errorElement, 'A data não pode ser no passado.');
        return false;
    } else {
        hideFieldError(errorElement);
        return true;
    }
}

// Validate time range
function validateTimeRange() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    if (!startTimeInput || !endTimeInput) return false;
    
    const startTime = startTimeInput.value;
    const endTime = endTimeInput.value;
    
    const startErrorElement = getOrCreateErrorElement(startTimeInput);
    const endErrorElement = getOrCreateErrorElement(endTimeInput);
    
    if (!startTime || !endTime) {
        hideFieldError(startErrorElement);
        hideFieldError(endErrorElement);
        return false;
    }
    
    const startMinutes = timeToMinutes(startTime);
    const endMinutes = timeToMinutes(endTime);
    
    if (startMinutes >= endMinutes) {
        showFieldError(endErrorElement, 'O horário de fim deve ser posterior ao de início.');
        return false;
    } else {
        hideFieldError(startErrorElement);
        hideFieldError(endErrorElement);
        return true;
    }
}

// Validate guests input
function validateGuests(guestsInput) {
    const guests = parseInt(guestsInput.value);
    const maxCapacity = parseInt(guestsInput.getAttribute('max')) || 50;
    
    const errorElement = getOrCreateErrorElement(guestsInput);
    
    if (guests < 1) {
        showFieldError(errorElement, 'Número mínimo de pessoas: 1.');
        return false;
    } else if (guests > maxCapacity) {
        showFieldError(errorElement, `Número máximo de pessoas: ${maxCapacity}.`);
        return false;
    } else {
        hideFieldError(errorElement);
        return true;
    }
}

// Validate entire form
function validateForm() {
    let isValid = true;
    
    // Validate date
    const dateInput = document.getElementById('reservation_date');
    if (dateInput && !validateDate(dateInput)) {
        isValid = false;
    }
    
    // Validate time range
    if (!validateTimeRange()) {
        isValid = false;
    }
    
    // Validate guests
    const guestsInput = document.getElementById('guests');
    if (guestsInput && !validateGuests(guestsInput)) {
        isValid = false;
    }
    
    return isValid;
}

// Helper function to convert time to minutes
function timeToMinutes(timeString) {
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
}

// Helper function to get or create error element
function getOrCreateErrorElement(input) {
    let errorElement = input.parentNode.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.style.color = '#dc3545';
        errorElement.style.fontSize = '0.875rem';
        errorElement.style.marginTop = '5px';
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

// Initialize modal functionality
function initializeModal() {
    const modal = document.getElementById('reservationModal');
    const closeBtn = document.querySelector('.close');
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeReservationModal();
            }
        });
    }
    
    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'block') {
            closeReservationModal();
        }
    });
}

// Open reservation modal
function openReservationModal(areaId, areaName, maxCapacity) {
    const modal = document.getElementById('reservationModal');
    const areaIdInput = document.getElementById('modal_area_id');
    const areaNameElement = document.getElementById('modal_area_name');
    const areaCapacityElement = document.getElementById('modal_area_capacity');
    const guestsInput = document.getElementById('guests');
    
    if (modal && areaIdInput && areaNameElement && areaCapacityElement) {
        // Set modal data
        areaIdInput.value = areaId;
        areaNameElement.textContent = areaName;
        areaCapacityElement.textContent = maxCapacity;
        
        // Set guests input max capacity
        if (guestsInput) {
            guestsInput.max = maxCapacity;
            guestsInput.value = Math.min(guestsInput.value || 1, maxCapacity);
        }
        
        // Reset form
        resetReservationForm();
        
        // Show modal
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        const firstInput = modal.querySelector('input');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
        
        // Add animation
        modal.classList.add('modal-open');
    }
}

// Close reservation modal
function closeReservationModal() {
    const modal = document.getElementById('reservationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        modal.classList.remove('modal-open');
        
        // Clear any error messages
        clearFormErrors();
    }
}

// Reset reservation form
function resetReservationForm() {
    const form = document.getElementById('leisureReservationForm');
    if (form) {
        form.reset();
        
        // Set default values
        const dateInput = document.getElementById('reservation_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
        
        const guestsInput = document.getElementById('guests');
        if (guestsInput) {
            guestsInput.value = '1';
        }
        
        // Clear any error messages
        clearFormErrors();
    }
}

// Clear form errors
function clearFormErrors() {
    const errorElements = document.querySelectorAll('.field-error');
    errorElements.forEach(element => {
        element.style.display = 'none';
        element.textContent = '';
    });
}

// Cancel leisure reservation
function cancelLeisureReservation(reservationId) {
    if (confirm('Tem certeza que deseja cancelar esta reserva?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.innerHTML = '<span class="loading"></span> Cancelando...';
        button.disabled = true;
        
        // Make AJAX request to cancel reservation
        fetch('api/cancel-leisure-reservation.php', {
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
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Erro de conexão. Tente novamente.', 'error');
            // Restore button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

// Show message
function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    
    const iconClass = type === 'error' ? 'exclamation-triangle' : 'check-circle';
    messageDiv.innerHTML = `
        <i class="fas fa-${iconClass}"></i>
        ${message}
    `;
    
    // Insert at the top of main content
    const main = document.querySelector('.accommodation-main .container');
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

// Utility function to format time
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    return `${hours}:${minutes}`;
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

// Add loading state to buttons
function addLoadingState(button, text = 'Carregando...') {
    button.dataset.originalText = button.textContent;
    button.innerHTML = `<span class="loading"></span> ${text}`;
    button.disabled = true;
}

// Remove loading state from buttons
function removeLoadingState(button) {
    button.textContent = button.dataset.originalText || 'Enviar';
    button.disabled = false;
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

// Initialize tooltips (if needed)
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const tooltipText = event.target.dataset.tooltip;
    if (!tooltipText) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Export functions for global access
window.accommodationUtils = {
    openReservationModal,
    closeReservationModal,
    cancelLeisureReservation,
    showMessage,
    formatTime,
    formatDate,
    isToday,
    isPastDate
};
