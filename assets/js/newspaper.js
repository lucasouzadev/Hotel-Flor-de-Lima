// Scripts específicos para o jornal O CORVO

document.addEventListener('DOMContentLoaded', function() {
    initializeNewspaperFeatures();
    setupStarRating();
    setupFormValidation();
    setupAutoSave();
});

// Inicializar funcionalidades do jornal
function initializeNewspaperFeatures() {
    // Animação de entrada dos artigos
    const articleCards = document.querySelectorAll('.article-card');
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

    articleCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Scroll suave para seções
    setupSmoothScrolling();
}

// Configurar sistema de avaliação por estrelas
function setupStarRating() {
    const starInputs = document.querySelectorAll('.stars input[type="radio"]');
    
    starInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            // Atualizar visual das estrelas
            updateStarDisplay(index + 1);
            
            // Salvar avaliação
            localStorage.setItem('tempRating', index + 1);
        });
        
        // Hover effect
        input.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
    });
    
    // Restaurar avaliação salva
    const savedRating = localStorage.getItem('tempRating');
    if (savedRating) {
        const ratingInput = document.querySelector(`input[value="${savedRating}"]`);
        if (ratingInput) {
            ratingInput.checked = true;
            updateStarDisplay(savedRating);
        }
    }
}

// Atualizar display das estrelas
function updateStarDisplay(rating) {
    const stars = document.querySelectorAll('.stars label.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#FFD700';
        } else {
            star.style.color = '#ddd';
        }
    });
}

// Destacar estrelas no hover
function highlightStars(rating) {
    const stars = document.querySelectorAll('.stars label.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#FFD700';
        } else {
            star.style.color = '#ddd';
        }
    });
}

// Configurar validação de formulários
function setupFormValidation() {
    const forms = document.querySelectorAll('.feedback-form-content, .comment-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Validação em tempo real
        const inputs = form.querySelectorAll('input, select, textarea');
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

// Validar formulário
function validateForm(form) {
    let isValid = true;
    const errors = [];
    
    // Validação específica para feedback
    if (form.querySelector('input[name="rating"]')) {
        const rating = form.querySelector('input[name="rating"]:checked');
        if (!rating) {
            errors.push('Por favor, selecione uma avaliação');
            isValid = false;
        }
        
        const category = form.querySelector('select[name="category"]');
        if (!category.value) {
            errors.push('Por favor, selecione uma categoria');
            isValid = false;
        }
    }
    
    // Validação de conteúdo
    const content = form.querySelector('textarea[name="content"]');
    if (content) {
        if (!content.value.trim()) {
            errors.push('Por favor, escreva um conteúdo');
            isValid = false;
        } else if (content.value.trim().length < 10) {
            errors.push('O conteúdo deve ter pelo menos 10 caracteres');
            isValid = false;
        } else if (content.value.trim().length > 1000) {
            errors.push('O conteúdo deve ter no máximo 1000 caracteres');
            isValid = false;
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
    
    // Validação de conteúdo
    if (name === 'content' && value) {
        if (value.length < 10) {
            showFieldError(field, 'Mínimo de 10 caracteres');
            return false;
        }
        if (value.length > 1000) {
            showFieldError(field, 'Máximo de 1000 caracteres');
            return false;
        }
    }
    
    // Validação de título
    if (name === 'title' && value && value.length > 200) {
        showFieldError(field, 'Máximo de 200 caracteres');
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
    
    // Inserir no início da página
    const main = document.querySelector('.newspaper-main .container');
    if (main) {
        main.insertBefore(errorContainer, main.firstChild);
        
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

// Configurar auto-save
function setupAutoSave() {
    const forms = document.querySelectorAll('.feedback-form-content, .comment-form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('input', debounce(() => {
                saveFormData(form);
            }, 1000));
        });
    });
    
    // Restaurar dados salvos
    restoreFormData();
}

// Salvar dados do formulário
function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem('newspaperFormData', JSON.stringify(data));
}

// Restaurar dados do formulário
function restoreFormData() {
    const savedData = localStorage.getItem('newspaperFormData');
    if (!savedData) return;
    
    try {
        const data = JSON.parse(savedData);
        
        // Restaurar campos
        Object.keys(data).forEach(key => {
            const field = document.querySelector(`[name="${key}"]`);
            if (field && field.type !== 'radio') {
                field.value = data[key];
            } else if (field && field.type === 'radio') {
                const radio = document.querySelector(`[name="${key}"][value="${data[key]}"]`);
                if (radio) radio.checked = true;
            }
        });
        
        // Limpar dados após restauração
        localStorage.removeItem('newspaperFormData');
    } catch (e) {
        console.error('Erro ao restaurar dados do formulário:', e);
    }
}

// Configurar scroll suave
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Filtrar por categoria
function filterByCategory(categoryName) {
    const articles = document.querySelectorAll('.article-card');
    
    articles.forEach(article => {
        const category = article.querySelector('.article-category').textContent.toLowerCase();
        
        if (categoryName === 'all' || category.includes(categoryName.toLowerCase())) {
            article.style.display = 'block';
        } else {
            article.style.display = 'none';
        }
    });
}

// Buscar artigos
function searchArticles(query) {
    const articles = document.querySelectorAll('.article-card');
    
    articles.forEach(article => {
        const title = article.querySelector('h3').textContent.toLowerCase();
        const content = article.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(query.toLowerCase()) || content.includes(query.toLowerCase())) {
            article.style.display = 'block';
        } else {
            article.style.display = 'none';
        }
    });
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

// Função debounce
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

// Contador de caracteres
function setupCharacterCounter() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength') || 1000;
        
        // Criar contador
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = `
            text-align: right;
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        `;
        
        textarea.parentNode.appendChild(counter);
        
        // Atualizar contador
        function updateCounter() {
            const length = textarea.value.length;
            counter.textContent = `${length}/${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.style.color = '#dc3545';
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#ffc107';
            } else {
                counter.style.color = '#666';
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

// Inicializar contador de caracteres
document.addEventListener('DOMContentLoaded', setupCharacterCounter);
