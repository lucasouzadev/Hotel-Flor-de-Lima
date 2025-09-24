// Scripts específicos para a página de reservas

document.addEventListener('DOMContentLoaded', function() {
    initializeReservationForm();
    setupRoomTypeSelection();
    setupDateValidation();
});

// Inicializar formulário de reserva
function initializeReservationForm() {
    const form = document.getElementById('reservationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateReservationForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Configurar data mínima para check-in (hoje)
    const checkInInput = document.getElementById('check_in');
    if (checkInInput) {
        const today = new Date().toISOString().split('T')[0];
        checkInInput.min = today;
    }
}

// Configurar seleção de tipos de quarto
function setupRoomTypeSelection() {
    const roomTypeCards = document.querySelectorAll('.room-type-card');
    const roomTypeSelect = document.getElementById('room_type_id');
    
    roomTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            const roomTypeId = this.dataset.roomTypeId;
            
            // Remover seleção anterior
            roomTypeCards.forEach(c => c.classList.remove('selected'));
            
            // Adicionar seleção atual
            this.classList.add('selected', 'selecting');
            
            // Atualizar select
            if (roomTypeSelect) {
                roomTypeSelect.value = roomTypeId;
                updateRoomInfo();
            }
            
            // Remover animação após completar
            setTimeout(() => {
                this.classList.remove('selecting');
            }, 300);
        });
    });
}

// Configurar validação de datas
function setupDateValidation() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const minCheckOut = new Date(checkInDate);
            minCheckOut.setDate(minCheckOut.getDate() + 1);
            
            checkOutInput.min = minCheckOut.toISOString().split('T')[0];
            
            if (checkOutInput.value && new Date(checkOutInput.value) <= checkInDate) {
                checkOutInput.value = '';
            }
            
            calculateTotal();
        });
        
        checkOutInput.addEventListener('change', function() {
            calculateTotal();
        });
    }
}

// Atualizar informações do quarto
function updateRoomInfo() {
    const roomTypeSelect = document.getElementById('room_type_id');
    const roomInfo = document.getElementById('roomInfo');
    const amenitiesList = document.getElementById('amenitiesList');
    const pricePerNight = document.getElementById('pricePerNight');
    
    if (!roomTypeSelect || !roomInfo) return;
    
    const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
    
    if (selectedOption.value) {
        const price = parseFloat(selectedOption.dataset.price);
        const amenities = JSON.parse(selectedOption.dataset.amenities || '[]');
        
        // Mostrar informações do quarto
        roomInfo.style.display = 'block';
        
        // Atualizar preço por noite
        if (pricePerNight) {
            pricePerNight.textContent = `R$ ${price.toFixed(2).replace('.', ',')}`;
        }
        
        // Atualizar lista de comodidades
        if (amenitiesList) {
            amenitiesList.innerHTML = '';
            amenities.forEach(amenity => {
                const li = document.createElement('li');
                li.textContent = amenity;
                amenitiesList.appendChild(li);
            });
        }
        
        // Calcular total
        calculateTotal();
        
        // Validar hóspedes
        validateGuests();
    } else {
        roomInfo.style.display = 'none';
    }
}

// Validar número de hóspedes
function validateGuests() {
    const roomTypeSelect = document.getElementById('room_type_id');
    const guestsSelect = document.getElementById('guests');
    
    if (!roomTypeSelect || !guestsSelect) return;
    
    const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
    
    if (selectedOption.value) {
        const maxOccupancy = parseInt(selectedOption.dataset.maxOccupancy);
        
        // Habilitar/desabilitar opções de hóspedes
        Array.from(guestsSelect.options).forEach(option => {
            if (option.value) {
                const guestCount = parseInt(option.value);
                option.disabled = guestCount > maxOccupancy;
                
                if (guestCount > maxOccupancy) {
                    option.textContent = `${guestCount} hóspede(s) - Excede capacidade`;
                } else {
                    option.textContent = `${guestCount} hóspede(s)`;
                }
            }
        });
        
        // Se o número atual de hóspedes excede a capacidade, resetar
        if (parseInt(guestsSelect.value) > maxOccupancy) {
            guestsSelect.value = '';
        }
    }
}

// Calcular total da reserva
function calculateTotal() {
    const roomTypeSelect = document.getElementById('room_type_id');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const numberOfNights = document.getElementById('numberOfNights');
    const totalAmount = document.getElementById('totalAmount');
    
    if (!roomTypeSelect || !checkInInput || !checkOutInput) return;
    
    const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
    
    if (selectedOption.value && checkInInput.value && checkOutInput.value) {
        const price = parseFloat(selectedOption.dataset.price);
        const checkInDate = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);
        
        const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        
        if (nights > 0) {
            // Atualizar número de noites
            if (numberOfNights) {
                numberOfNights.textContent = nights;
            }
            
            // Atualizar total
            if (totalAmount) {
                const total = price * nights;
                totalAmount.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
            }
        } else {
            if (numberOfNights) numberOfNights.textContent = '0';
            if (totalAmount) totalAmount.textContent = 'R$ 0,00';
        }
    } else {
        if (numberOfNights) numberOfNights.textContent = '0';
        if (totalAmount) totalAmount.textContent = 'R$ 0,00';
    }
}

// Validar formulário de reserva
function validateReservationForm() {
    let isValid = true;
    const errors = [];
    
    // Validar tipo de quarto
    const roomTypeSelect = document.getElementById('room_type_id');
    if (!roomTypeSelect.value) {
        errors.push('Selecione um tipo de quarto');
        isValid = false;
    }
    
    // Validar datas
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (!checkInInput.value) {
        errors.push('Selecione a data de check-in');
        isValid = false;
    }
    
    if (!checkOutInput.value) {
        errors.push('Selecione a data de check-out');
        isValid = false;
    }
    
    if (checkInInput.value && checkOutInput.value) {
        const checkInDate = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (checkInDate < today) {
            errors.push('A data de check-in não pode ser no passado');
            isValid = false;
        }
        
        if (checkOutDate <= checkInDate) {
            errors.push('A data de check-out deve ser posterior ao check-in');
            isValid = false;
        }
        
        const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        if (nights > 30) {
            errors.push('O período máximo de estadia é de 30 dias');
            isValid = false;
        }
    }
    
    // Validar número de hóspedes
    const guestsSelect = document.getElementById('guests');
    if (!guestsSelect.value) {
        errors.push('Selecione o número de hóspedes');
        isValid = false;
    }
    
    // Validar capacidade do quarto
    if (roomTypeSelect.value && guestsSelect.value) {
        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        const maxOccupancy = parseInt(selectedOption.dataset.maxOccupancy);
        const guestCount = parseInt(guestsSelect.value);
        
        if (guestCount > maxOccupancy) {
            errors.push(`O número de hóspedes (${guestCount}) excede a capacidade do quarto (${maxOccupancy})`);
            isValid = false;
        }
    }
    
    // Mostrar erros
    if (!isValid) {
        showValidationErrors(errors);
    }
    
    return isValid;
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
    
    // Inserir antes do formulário
    const form = document.getElementById('reservationForm');
    if (form) {
        form.parentNode.insertBefore(errorContainer, form);
        
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

// Verificar disponibilidade em tempo real
function checkAvailability() {
    const roomTypeSelect = document.getElementById('room_type_id');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (roomTypeSelect.value && checkInInput.value && checkOutInput.value) {
        // Aqui você faria uma requisição AJAX para verificar disponibilidade
        // Por enquanto, vamos simular
        console.log('Verificando disponibilidade...');
    }
}

// Debounce para verificação de disponibilidade
const debounceAvailability = debounce(checkAvailability, 500);

// Aplicar debounce aos campos relevantes
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['room_type_id', 'check_in', 'check_out'];
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('change', debounceAvailability);
        }
    });
});

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
