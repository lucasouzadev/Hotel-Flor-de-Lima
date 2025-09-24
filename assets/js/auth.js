// Scripts específicos para páginas de autenticação

document.addEventListener('DOMContentLoaded', function() {
    // Formatação de campos
    formatPhoneField();
    formatDocumentField();
    
    // Validação de formulários
    setupFormValidation();
    
    // Auto-hide messages
    autoHideMessages();
});

// Formatar campo de telefone
function formatPhoneField() {
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
}

// Formatar campo de CPF
function formatDocumentField() {
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

// Configurar validação de formulários
function setupFormValidation() {
    const forms = document.querySelectorAll('.auth-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Validação em tempo real
        const inputs = form.querySelectorAll('input[required]');
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

// Validar formulário completo
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Validação específica para senha e confirmação
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword) {
        if (password.value !== confirmPassword.value) {
            showFieldError(confirmPassword, 'As senhas não coincidem');
            isValid = false;
        }
    }
    
    // Validação de idade mínima
    const birthDate = form.querySelector('input[name="birth_date"]');
    if (birthDate && birthDate.value) {
        const age = calculateAge(birthDate.value);
        if (age < 18) {
            showFieldError(birthDate, 'Você deve ter pelo menos 18 anos');
            isValid = false;
        }
    }
    
    return isValid;
}

// Validar campo individual
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const name = field.name;
    
    // Campo obrigatório vazio
    if (field.required && !value) {
        showFieldError(field, 'Este campo é obrigatório');
        return false;
    }
    
    // Validação de email
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Email inválido');
            return false;
        }
    }
    
    // Validação de senha
    if (name === 'password' && value && value.length < 6) {
        showFieldError(field, 'A senha deve ter pelo menos 6 caracteres');
        return false;
    }
    
    // Validação de nome
    if (name === 'name' && value && value.length < 2) {
        showFieldError(field, 'O nome deve ter pelo menos 2 caracteres');
        return false;
    }
    
    // Validação de telefone
    if (name === 'phone' && value) {
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Formato de telefone inválido');
            return false;
        }
    }
    
    // Validação de CPF
    if (name === 'document' && value) {
        if (!validateCPF(value)) {
            showFieldError(field, 'CPF inválido');
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

// Calcular idade
function calculateAge(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

// Auto-hide messages
function autoHideMessages() {
    const messages = document.querySelectorAll('.message');
    
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
}

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Add CSS for field errors
const style = document.createElement('style');
style.textContent = `
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
    
    .message {
        transition: opacity 0.3s ease;
    }
`;
document.head.appendChild(style);
