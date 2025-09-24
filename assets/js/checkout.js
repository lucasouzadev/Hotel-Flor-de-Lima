// Scripts específicos para a página de checkout

document.addEventListener('DOMContentLoaded', function() {
    initializeCheckout();
    loadCartItems();
    setupFormValidation();
    setupStepNavigation();
    setupPaymentMethods();
});

// Inicializar checkout
function initializeCheckout() {
    // Verificar se há itens no carrinho
    const cart = getCartFromStorage();
    if (!cart || cart.length === 0) {
        showEmptyCart();
        return;
    }
    
    // Configurar data mínima
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = today;
    });
}

// Carregar itens do carrinho
function loadCartItems() {
    const cart = getCartFromStorage();
    const cartItemsContainer = document.getElementById('cartItems');
    
    if (!cart || cart.length === 0) {
        showEmptyCart();
        return;
    }
    
    // Filtrar apenas drinks
    const drinkItems = cart.filter(item => item.type === 'drink');
    
    if (drinkItems.length === 0) {
        showEmptyCart();
        return;
    }
    
    // Renderizar itens
    cartItemsContainer.innerHTML = '';
    let subtotal = 0;
    
    drinkItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <div class="item-info">
                <div class="item-name">${item.name}</div>
                <div class="item-details">Qtd: ${item.quantity} x R$ ${item.price.toFixed(2).replace('.', ',')}</div>
            </div>
            <div class="item-price">R$ ${itemTotal.toFixed(2).replace('.', ',')}</div>
        `;
        
        cartItemsContainer.appendChild(itemElement);
    });
    
    // Calcular totais
    const serviceFee = subtotal * 0.1; // 10% taxa de serviço
    const total = subtotal + serviceFee;
    
    // Atualizar totais na sidebar
    updateSidebarTotals(subtotal, serviceFee, total);
    
    // Atualizar totais na confirmação
    updateConfirmationTotals(drinkItems, subtotal, serviceFee, total);
}

// Mostrar carrinho vazio
function showEmptyCart() {
    const cartItemsContainer = document.getElementById('cartItems');
    cartItemsContainer.innerHTML = `
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Carrinho Vazio</h3>
            <p>Você não tem itens no carrinho.</p>
            <a href="bar-celina.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i>
                Ir para o Bar
            </a>
        </div>
    `;
}

// Atualizar totais na sidebar
function updateSidebarTotals(subtotal, serviceFee, total) {
    const subtotalElement = document.getElementById('subtotal');
    const serviceFeeElement = document.getElementById('serviceFee');
    const totalElement = document.getElementById('total');
    
    if (subtotalElement) subtotalElement.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    if (serviceFeeElement) serviceFeeElement.textContent = `R$ ${serviceFee.toFixed(2).replace('.', ',')}`;
    if (totalElement) totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Atualizar totais na confirmação
function updateConfirmationTotals(drinkItems, subtotal, serviceFee, total) {
    const orderItemsContainer = document.getElementById('orderItems');
    const orderTotalElement = document.getElementById('orderTotal');
    
    if (orderItemsContainer) {
        orderItemsContainer.innerHTML = '';
        
        drinkItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            const itemElement = document.createElement('div');
            itemElement.className = 'order-item';
            itemElement.innerHTML = `
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-details">Qtd: ${item.quantity} x R$ ${item.price.toFixed(2).replace('.', ',')}</div>
                </div>
                <div class="item-price">R$ ${itemTotal.toFixed(2).replace('.', ',')}</div>
            `;
            
            orderItemsContainer.appendChild(itemElement);
        });
        
        // Adicionar resumo de totais
        const summaryElement = document.createElement('div');
        summaryElement.className = 'order-summary-breakdown';
        summaryElement.innerHTML = `
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>R$ ${subtotal.toFixed(2).replace('.', ',')}</span>
            </div>
            <div class="summary-row">
                <span>Taxa de serviço (10%):</span>
                <span>R$ ${serviceFee.toFixed(2).replace('.', ',')}</span>
            </div>
        `;
        
        orderItemsContainer.appendChild(summaryElement);
    }
    
    if (orderTotalElement) {
        orderTotalElement.textContent = total.toFixed(2).replace('.', ',');
    }
}

// Configurar validação de formulário
function setupFormValidation() {
    const form = document.getElementById('checkoutForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        if (!validateCheckoutForm()) {
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
}

// Validar formulário de checkout
function validateCheckoutForm() {
    let isValid = true;
    const errors = [];
    
    // Validar informações de cobrança
    const requiredFields = [
        'billing_name', 'billing_email', 'billing_phone', 
        'billing_address', 'billing_city', 'billing_state', 'billing_zip'
    ];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field || !field.value.trim()) {
            errors.push(`${getFieldLabel(fieldName)} é obrigatório`);
            isValid = false;
        }
    });
    
    // Validar email
    const email = document.getElementById('billing_email');
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            errors.push('Email inválido');
            isValid = false;
        }
    }
    
    // Validar telefone
    const phone = document.getElementById('billing_phone');
    if (phone && phone.value) {
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        if (!phoneRegex.test(phone.value)) {
            errors.push('Formato de telefone inválido');
            isValid = false;
        }
    }
    
    // Validar CEP
    const zip = document.getElementById('billing_zip');
    if (zip && zip.value) {
        const zipRegex = /^\d{5}-?\d{3}$/;
        if (!zipRegex.test(zip.value.replace(/\D/g, ''))) {
            errors.push('CEP inválido');
            isValid = false;
        }
    }
    
    // Validar método de pagamento
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        errors.push('Selecione um método de pagamento');
        isValid = false;
    }
    
    // Validar termos
    const termsAgreement = document.getElementById('terms_agreement');
    if (!termsAgreement || !termsAgreement.checked) {
        errors.push('Você deve aceitar os termos de uso');
        isValid = false;
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
    if (name === 'billing_email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Email inválido');
            return false;
        }
    }
    
    // Validação de telefone
    if (name === 'billing_phone' && value) {
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Formato: (XX) XXXXX-XXXX');
            return false;
        }
    }
    
    // Validação de CEP
    if (name === 'billing_zip' && value) {
        const zipRegex = /^\d{5}-?\d{3}$/;
        if (!zipRegex.test(value.replace(/\D/g, ''))) {
            showFieldError(field, 'CEP inválido');
            return false;
        }
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
    const content = document.querySelector('.checkout-content .container');
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

// Configurar navegação entre steps
function setupStepNavigation() {
    window.changeStep = function(direction) {
        const currentStep = document.querySelector('.form-step.active');
        const currentStepNumber = parseInt(currentStep.dataset.step);
        const nextStepNumber = currentStepNumber + direction;
        
        // Validar step atual antes de avançar
        if (direction > 0 && !validateCurrentStep(currentStepNumber)) {
            return;
        }
        
        // Mostrar próximo step
        if (nextStepNumber >= 1 && nextStepNumber <= 3) {
            showStep(nextStepNumber);
            updateStepNavigation(nextStepNumber);
        }
    };
}

// Validar step atual
function validateCurrentStep(stepNumber) {
    const currentStep = document.querySelector(`.form-step[data-step="${stepNumber}"]`);
    const requiredFields = currentStep.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Mostrar step específico
function showStep(stepNumber) {
    // Esconder todos os steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Mostrar step atual
    const currentStep = document.querySelector(`.form-step[data-step="${stepNumber}"]`);
    if (currentStep) {
        currentStep.classList.add('active');
    }
    
    // Atualizar steps no topo
    document.querySelectorAll('.step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'completed');
        
        if (stepNum < stepNumber) {
            step.classList.add('completed');
        } else if (stepNum === stepNumber) {
            step.classList.add('active');
        }
    });
}

// Atualizar navegação dos steps
function updateStepNavigation(stepNumber) {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Botão anterior
    if (prevBtn) {
        prevBtn.style.display = stepNumber > 1 ? 'block' : 'none';
    }
    
    // Botão próximo/submit
    if (stepNumber < 3) {
        if (nextBtn) nextBtn.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'none';
    } else {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'block';
    }
}

// Configurar métodos de pagamento
function setupPaymentMethods() {
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Remover seleção anterior
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Adicionar seleção atual
            this.closest('.payment-option').classList.add('selected');
        });
    });
}

// Formatação de campos
function setupFieldFormatting() {
    // Formatar telefone
    const phoneInput = document.getElementById('billing_phone');
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
    
    // Formatar CEP
    const zipInput = document.getElementById('billing_zip');
    if (zipInput) {
        zipInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 8) {
                value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }
}

// Obter carrinho do localStorage
function getCartFromStorage() {
    try {
        const cart = localStorage.getItem('cart');
        return cart ? JSON.parse(cart) : [];
    } catch (e) {
        console.error('Erro ao carregar carrinho:', e);
        return [];
    }
}

// Obter label do campo
function getFieldLabel(fieldName) {
    const labels = {
        'billing_name': 'Nome',
        'billing_email': 'Email',
        'billing_phone': 'Telefone',
        'billing_address': 'Endereço',
        'billing_city': 'Cidade',
        'billing_state': 'Estado',
        'billing_zip': 'CEP'
    };
    
    return labels[fieldName] || fieldName;
}

// Inicializar formatação de campos
document.addEventListener('DOMContentLoaded', setupFieldFormatting);
