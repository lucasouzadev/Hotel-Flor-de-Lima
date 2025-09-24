// Scripts específicos para o dashboard

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupModalHandlers();
    setupFormValidation();
    setupFieldFormatting();
});

// Inicializar dashboard
function initializeDashboard() {
    // Animação de entrada dos cards
    const cards = document.querySelectorAll('.stat-card, .action-card, .reservation-item, .order-item');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Atualizar contadores em tempo real
    updateStatsCounters();
}

// Configurar handlers dos modais
function setupModalHandlers() {
    // Fechar modal ao clicar fora
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="block"]');
            if (openModal) {
                openModal.style.display = 'none';
            }
        }
    });
}

// Configurar validação de formulários
function setupFormValidation() {
    const forms = document.querySelectorAll('.profile-form, .password-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Validação em tempo real
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

// Configurar formatação de campos
function setupFieldFormatting() {
    // Formatar telefone
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            
            e.target.value = value;
        });
    }
    
    // Formatar CPF
    const documentInput = document.getElementById('document');
    if (documentInput) {
        documentInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length >= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
}

// Validar formulário
function validateForm(form) {
    let isValid = true;
    const errors = [];
    
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Validação específica para senha
    if (form.classList.contains('password-form')) {
        const newPassword = form.querySelector('input[name="new_password"]');
        const confirmPassword = form.querySelector('input[name="confirm_password"]');
        
        if (newPassword && confirmPassword) {
            if (newPassword.value !== confirmPassword.value) {
                errors.push('As senhas não coincidem');
                isValid = false;
            }
            
            if (newPassword.value.length < 6) {
                errors.push('A nova senha deve ter pelo menos 6 caracteres');
                isValid = false;
            }
        }
    }
    
    // Validação específica para perfil
    if (form.classList.contains('profile-form')) {
        const name = form.querySelector('input[name="name"]');
        if (name && name.value.length < 2) {
            errors.push('Nome deve ter pelo menos 2 caracteres');
            isValid = false;
        }
        
        const phone = form.querySelector('input[name="phone"]');
        if (phone && phone.value) {
            const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
            if (!phoneRegex.test(phone.value)) {
                errors.push('Formato de telefone inválido');
                isValid = false;
            }
        }
        
        const document = form.querySelector('input[name="document"]');
        if (document && document.value) {
            if (!validateCPF(document.value)) {
                errors.push('CPF inválido');
                isValid = false;
            }
        }
    }
    
    // Mostrar erros
    if (!isValid) {
        showValidationErrors(errors);
    }
    
    return isValid;
}

// Validar campo individual
function validateField(field) {
    const value = field.value.trim();
    const name = field.name;
    
    // Campo obrigatório vazio
    if (field.required && !value) {
        showFieldError(field, 'Este campo é obrigatório');
        return false;
    }
    
    // Validação de email
    if (name === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Email inválido');
            return false;
        }
    }
    
    // Validação de senha
    if (name === 'new_password' && value && value.length < 6) {
        showFieldError(field, 'Senha deve ter pelo menos 6 caracteres');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

// Mostrar erro no campo
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Limpar erro do campo
function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Mostrar erros de validação
function showValidationErrors(errors) {
    // Remover erros anteriores
    const existingErrors = document.querySelectorAll('.validation-errors');
    existingErrors.forEach(error => error.remove());
    
    // Criar container de erros
    const errorContainer = document.createElement('div');
    errorContainer.className = 'validation-errors message message-error';
    errorContainer.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Por favor, corrija os seguintes erros:</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
        </div>
    `;
    
    // Inserir no início do conteúdo
    const content = document.querySelector('.dashboard-content .container');
    if (content) {
        content.insertBefore(errorContainer, content.firstChild);
        
        // Scroll para o erro
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Remover erro após 10 segundos
        setTimeout(() => {
            if (errorContainer.parentNode) {
                errorContainer.remove();
            }
        }, 10000);
    }
}

// Validar CPF
function validateCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let remainder = 11 - (sum % 11);
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(9))) return false;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    remainder = 11 - (sum % 11);
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(10))) return false;
    
    return true;
}

// Atualizar contadores das estatísticas
function updateStatsCounters() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const finalValue = stat.textContent;
        const isNumber = !isNaN(parseFloat(finalValue.replace(/[^\d.-]/g, '')));
        
        if (isNumber) {
            const numericValue = parseFloat(finalValue.replace(/[^\d.-]/g, ''));
            animateCounter(stat, 0, numericValue, 2000, finalValue.includes('R$') ? 'currency' : 'number');
        }
    });
}

// Animar contador
function animateCounter(element, start, end, duration, type = 'number') {
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = start + (end - start) * progress;
        
        if (type === 'currency') {
            element.textContent = `R$ ${current.toFixed(2).replace('.', ',')}`;
        } else {
            element.textContent = Math.floor(current).toString();
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

// Toggle modal
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
        } else {
            modal.style.display = 'block';
        }
    }
}

// Cancelar reserva
function cancelReservation(reservationId) {
    if (confirm('Tem certeza que deseja cancelar esta reserva?')) {
        // Simular cancelamento (aqui você faria uma requisição AJAX real)
        showNotification('Reserva cancelada com sucesso!', 'success');
        
        // Recarregar a página após um delay
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
}

// Check-in
function checkIn(reservationId) {
    if (confirm('Deseja fazer o check-in agora?')) {
        // Simular check-in (aqui você faria uma requisição AJAX real)
        showNotification('Check-in realizado com sucesso! Bem-vindo ao Hotel Flor de Lima!', 'success');
        
        // Recarregar a página após um delay
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
}

// Mostrar notificação
function showNotification(message, type = 'success') {
    // Remover notificações existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Criar notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Estilos da notificação
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10001;
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Adicionar estilos para notificações
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        margin-left: 10px;
    }
`;
document.head.appendChild(notificationStyles);

// Adicionar estilos para validação
const validationStyles = document.createElement('style');
validationStyles.textContent = `
    .form-group input.error {
        border-color: #dc3545;
        background-color: #fff5f5;
    }
    
    .field-error {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .field-error:before {
        content: "⚠";
        font-size: 0.8rem;
    }
`;
document.head.appendChild(validationStyles);
