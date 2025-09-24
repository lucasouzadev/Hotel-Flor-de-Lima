// Scripts específicos para o Bar Celina

document.addEventListener('DOMContentLoaded', function() {
    initializeBarFeatures();
    setupCartModal();
    setupQuantitySelectors();
});

// Inicializar funcionalidades do bar
function initializeBarFeatures() {
    // Animação de entrada dos cards de drinks
    const drinkCards = document.querySelectorAll('.drink-card');
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

    drinkCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Efeito parallax no hero (removido para evitar conflito com header fixo)
    // window.addEventListener('scroll', () => {
    //     const scrolled = window.pageYOffset;
    //     const hero = document.querySelector('.bar-hero');
    //     if (hero) {
    //         hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    //     }
    // });
}

// Configurar modal do carrinho
function setupCartModal() {
    const modal = document.getElementById('cartModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCart);
    }
    
    if (modal) {
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeCart();
            }
        });
    }
    
    // Atualizar contador do carrinho no header se existir
    updateCartCounter();
}

// Alterar quantidade
function changeQuantity(drinkId, change) {
    const input = document.getElementById(`qty-${drinkId}`);
    if (input) {
        let newValue = parseInt(input.value) + change;
        if (newValue < 1) newValue = 1;
        if (newValue > 10) newValue = 10;
        input.value = newValue;
    }
}

// Configurar seletores de quantidade
function setupQuantitySelectors() {
    document.addEventListener('click', (e) => {
        if (e.target.closest('.qty-btn')) {
            e.preventDefault();
        }
    });
}

// Adicionar ao carrinho
function addToCart(drinkId, price, name) {
    const quantityInput = document.getElementById(`qty-${drinkId}`);
    const quantity = parseInt(quantityInput?.value || 1);
    
    // Verificar se o usuário está logado
    const isLoggedIn = document.body.classList.contains('logged-in') || 
                      document.querySelector('.nav-menu a[href="logout.php"]');
    
    if (!isLoggedIn) {
        showNotification('Você precisa fazer login para adicionar itens ao carrinho.', 'error');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }
    
    // Simular adição ao carrinho
    const cartItem = {
        id: drinkId,
        name: name,
        price: parseFloat(price),
        quantity: quantity,
        type: 'drink',
        addedAt: new Date().toISOString()
    };
    
    // Adicionar ao localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Verificar se o item já existe
    const existingItem = cart.find(item => item.id === drinkId && item.type === 'drink');
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push(cartItem);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Mostrar notificação
    showNotification(`${name} adicionado ao carrinho!`, 'success');
    
    // Atualizar contador
    updateCartCounter();
    
    // Mostrar modal do carrinho
    showCart();
}

// Mostrar carrinho
function showCart() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        updateCartDisplay();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Fechar carrinho
function closeCart() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Atualizar exibição do carrinho
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartTotal = document.getElementById('cartTotal');
    
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const drinkItems = cart.filter(item => item.type === 'drink');
    
    if (drinkItems.length === 0) {
        cartItems.style.display = 'none';
        cartEmpty.style.display = 'block';
        cartTotal.textContent = '0,00';
    } else {
        cartEmpty.style.display = 'none';
        cartItems.style.display = 'block';
        cartItems.innerHTML = '';
        
        let total = 0;
        
        drinkItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">R$ ${item.price.toFixed(2).replace('.', ',')} cada</div>
                </div>
                <div class="cart-item-actions">
                    <div class="cart-item-qty">
                        <button type="button" onclick="updateCartItemQuantity(${item.id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="${item.quantity}" min="1" max="10" 
                               onchange="updateCartItemQuantity(${item.id}, 0, this.value)">
                        <button type="button" onclick="updateCartItemQuantity(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="remove-item" onclick="removeFromCart(${item.id}, 'drink')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            cartItems.appendChild(itemElement);
        });
        
        cartTotal.textContent = total.toFixed(2).replace('.', ',');
    }
}

// Atualizar quantidade do item no carrinho
function updateCartItemQuantity(itemId, change, newValue = null) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const item = cart.find(cartItem => cartItem.id === itemId && cartItem.type === 'drink');
    
    if (item) {
        if (newValue !== null) {
            item.quantity = parseInt(newValue);
        } else {
            item.quantity += change;
        }
        
        if (item.quantity < 1) {
            cart = cart.filter(cartItem => !(cartItem.id === itemId && cartItem.type === 'drink'));
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
        updateCartCounter();
    }
}

// Remover do carrinho
function removeFromCart(itemId, type) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => !(item.id === itemId && item.type === type));
    localStorage.setItem('cart', JSON.stringify(cart));
    
    updateCartDisplay();
    updateCartCounter();
    showNotification('Item removido do carrinho!', 'success');
}

// Atualizar contador do carrinho
function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const drinkItems = cart.filter(item => item.type === 'drink');
    const totalItems = drinkItems.reduce((sum, item) => sum + item.quantity, 0);
    
    // Atualizar contador no header se existir
    const counter = document.querySelector('.cart-counter');
    if (counter) {
        counter.textContent = totalItems;
        counter.style.display = totalItems > 0 ? 'block' : 'none';
    }
    
    // Adicionar contador ao menu se não existir
    const navMenu = document.querySelector('.nav-menu');
    if (navMenu && totalItems > 0 && !document.querySelector('.cart-counter')) {
        const cartLink = document.createElement('li');
        cartLink.innerHTML = `
            <a href="#" onclick="showCart(); return false;" class="cart-link">
                <i class="fas fa-shopping-cart"></i>
                Carrinho <span class="cart-counter">${totalItems}</span>
            </a>
        `;
        navMenu.appendChild(cartLink);
    }
}

// Finalizar pedido
function checkout() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const drinkItems = cart.filter(item => item.type === 'drink');
    
    if (drinkItems.length === 0) {
        showNotification('Seu carrinho está vazio!', 'error');
        return;
    }
    
    // Mostrar modal de finalização
    showCheckoutModal(drinkItems);
}

// Filtrar drinks por categoria
function filterDrinksByCategory(categoryName) {
    const categories = document.querySelectorAll('.menu-category');
    
    categories.forEach(category => {
        const title = category.querySelector('.category-title').textContent.toLowerCase();
        if (categoryName === 'all' || title.includes(categoryName.toLowerCase())) {
            category.style.display = 'block';
        } else {
            category.style.display = 'none';
        }
    });
}

// Buscar drinks
function searchDrinks(query) {
    const drinkCards = document.querySelectorAll('.drink-card');
    
    drinkCards.forEach(card => {
        const name = card.querySelector('.drink-name').textContent.toLowerCase();
        const description = card.querySelector('.drink-description').textContent.toLowerCase();
        const ingredients = card.querySelector('.ingredients span').textContent.toLowerCase();
        
        if (name.includes(query.toLowerCase()) || 
            description.includes(query.toLowerCase()) || 
            ingredients.includes(query.toLowerCase())) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Ordenar drinks
function sortDrinks(sortBy) {
    const categories = document.querySelectorAll('.menu-category');
    
    categories.forEach(category => {
        const drinksGrid = category.querySelector('.drinks-grid');
        const drinkCards = Array.from(drinksGrid.querySelectorAll('.drink-card'));
        
        drinkCards.sort((a, b) => {
            switch (sortBy) {
                case 'price-low':
                    const priceA = parseFloat(a.querySelector('.price').textContent.replace('R$ ', '').replace(',', '.'));
                    const priceB = parseFloat(b.querySelector('.price').textContent.replace('R$ ', '').replace(',', '.'));
                    return priceA - priceB;
                    
                case 'price-high':
                    const priceA2 = parseFloat(a.querySelector('.price').textContent.replace('R$ ', '').replace(',', '.'));
                    const priceB2 = parseFloat(b.querySelector('.price').textContent.replace('R$ ', '').replace(',', '.'));
                    return priceB2 - priceA2;
                    
                case 'name':
                    const nameA = a.querySelector('.drink-name').textContent;
                    const nameB = b.querySelector('.drink-name').textContent;
                    return nameA.localeCompare(nameB);
                    
                default:
                    return 0;
            }
        });
        
        drinkCards.forEach(card => drinksGrid.appendChild(card));
    });
}

// Mostrar notificação
function showNotification(message, type = 'success', duration = 5000) {
    // Remover notificações existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Definir cores e ícones por tipo
    const typeConfig = {
        success: { 
            bg: 'linear-gradient(135deg, #28a745, #20c997)', 
            icon: 'fas fa-check-circle' 
        },
        error: { 
            bg: 'linear-gradient(135deg, #dc3545, #e74c3c)', 
            icon: 'fas fa-exclamation-circle' 
        },
        warning: { 
            bg: 'linear-gradient(135deg, #ffc107, #fd7e14)', 
            icon: 'fas fa-exclamation-triangle' 
        },
        info: { 
            bg: 'linear-gradient(135deg, #17a2b8, #6f42c1)', 
            icon: 'fas fa-info-circle' 
        }
    };
    
    const config = typeConfig[type] || typeConfig.info;
    
    // Criar notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="${config.icon}"></i>
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
        background: ${config.bg};
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        z-index: 10001;
        animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        max-width: 400px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover após duração especificada
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
}

// Adicionar estilos para notificações
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        0% { 
            transform: translateX(100%) scale(0.8); 
            opacity: 0; 
        }
        100% { 
            transform: translateX(0) scale(1); 
            opacity: 1; 
        }
    }
    
    @keyframes slideOutRight {
        0% { 
            transform: translateX(0) scale(1); 
            opacity: 1; 
        }
        100% { 
            transform: translateX(100%) scale(0.8); 
            opacity: 0; 
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .notification-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
    }
    
    .notification-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }
    
    .cart-link {
        position: relative;
    }
    
    .cart-counter {
        background: linear-gradient(135deg, #ff4757, #ff3742);
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.8rem;
        margin-left: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
`;
document.head.appendChild(notificationStyles);

// Modal de finalização de pedido
function showCheckoutModal(drinkItems) {
    // Criar modal se não existir
    let modal = document.getElementById('checkoutModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'checkoutModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-shopping-cart"></i> Finalizar Pedido</h3>
                    <span class="close" onclick="closeCheckoutModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="order-summary">
                        <h4>Resumo do Pedido:</h4>
                        <div id="orderItemsList"></div>
                        <div class="order-total">
                            <strong>Total: R$ <span id="orderTotal">0,00</span></strong>
                        </div>
                    </div>
                    
                    <form id="checkoutForm">
                        <div class="form-group">
                            <label for="tableNumber">Número da Mesa (opcional):</label>
                            <input type="text" id="tableNumber" name="tableNumber" placeholder="Ex: Mesa 5, Balcão 3">
                        </div>
                        
                        <div class="form-group">
                            <label for="orderNotes">Observações (opcional):</label>
                            <textarea id="orderNotes" name="orderNotes" rows="3" placeholder="Instruções especiais, pedidos de moderação..."></textarea>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Confirmar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Preencher resumo do pedido
    const itemsList = document.getElementById('orderItemsList');
    const totalElement = document.getElementById('orderTotal');
    
    let total = 0;
    let itemsHtml = '';
    
    drinkItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        itemsHtml += `
            <div class="order-item">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">${item.quantity}x</span>
                <span class="item-price">R$ ${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });
    
    itemsList.innerHTML = itemsHtml;
    totalElement.textContent = total.toFixed(2);
    
    // Mostrar modal
    modal.style.display = 'block';
    
    // Configurar submit do formulário
    const form = document.getElementById('checkoutForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        processOrder(drinkItems);
    };
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function processOrder(drinkItems) {
    const tableNumber = document.getElementById('tableNumber').value;
    const notes = document.getElementById('orderNotes').value;
    
    // Preparar dados do pedido
    const orderData = {
        items: drinkItems.map(item => ({
            drink_id: item.id,
            quantity: item.quantity
        })),
        table_number: tableNumber || null,
        notes: notes || ''
    };
    
    // Fechar modal
    closeCheckoutModal();
    
    // Mostrar loading
    showNotification('Processando pedido...', 'info');
    
    // Enviar pedido para o servidor
    fetch('api/process-bar-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        // Verificar se a resposta é válida
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Verificar se o conteúdo é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Resposta não é JSON válido');
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(`Pedido realizado com sucesso! Total: R$ ${data.total_amount.toFixed(2)}`, 'success');
            
            // Limpar carrinho
            localStorage.removeItem('cart');
            updateCartDisplay();
            
            // Atualizar contador do dashboard se existir
            if (typeof updateDashboardData === 'function') {
                updateDashboardData();
            }
        } else {
            showNotification(data.message || 'Erro ao processar pedido', 'error');
        }
    })
    .catch(error => {
        console.error('Erro no pedido:', error);
        showNotification('Erro ao processar pedido. Verifique sua conexão e tente novamente.', 'error');
    });
}

// Inicializar carrinho ao carregar a página
updateCartCounter();
