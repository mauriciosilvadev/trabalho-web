/**
 * Sistema de Carrinho de Compras
 * Gerencia carrinho usando sessões PHP + LocalStorage
 */

// Variables globais
let cart = {
    items: [],
    total: 0
};

$(document).ready(function() {
    // Initialize cart
    loadCart();
    updateCartDisplay();
    
    // Event handlers
    $(document).on('click', '.add-to-cart', handleAddToCart);
    $(document).on('click', '.remove-from-cart', handleRemoveFromCart);
    $(document).on('click', '.select-date', handleSelectDate);
    $(document).on('click', '#clearCart', clearCart);
    $(document).on('click', '#proceedToCheckout', proceedToCheckout);
    
    // Show cart page items if on cart page
    if (window.location.pathname.includes('carrinho.php')) {
        displayCartItems();
    }
});

/**
 * Load cart from localStorage and sync with session
 */
function loadCart() {
    try {
        const storedCart = localStorage.getItem('service_cart');
        if (storedCart) {
            cart = JSON.parse(storedCart);
        }
        
        // Sync with PHP session if needed
        syncCartWithSession();
    } catch (e) {
        console.error('Error loading cart:', e);
        cart = { items: [], total: 0 };
    }
}

/**
 * Save cart to localStorage and PHP session
 */
function saveCart() {
    try {
        localStorage.setItem('service_cart', JSON.stringify(cart));
        updateCartDisplay();
        
        // Sync with PHP session
        syncCartWithSession();
    } catch (e) {
        console.error('Error saving cart:', e);
    }
}

/**
 * Sync cart with PHP session
 */
function syncCartWithSession() {
    // Determinar o caminho correto baseado na URL atual
    const currentPath = window.location.pathname;
    let syncUrl;
    
    if (currentPath.includes('/admin/')) {
        // Estamos na área admin
        syncUrl = 'sync_cart.php';
    } else {
        // Estamos na área pública
        syncUrl = 'sync_cart.php';
    }
    
    $.ajax({
        url: syncUrl,
        method: 'POST',
        data: { cart: JSON.stringify(cart) },
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                console.error('Cart sync failed:', response.message);
            }
        },
        error: function() {
            console.error('Cart sync request failed');
        }
    });
}

/**
 * Add service to cart (global function)
 */
function addToCart(service) {
    // Check cart limit
    if (cart.items.length >= 5) {
        showError('Carrinho pode ter no máximo 5 serviços');
        return;
    }
    
    // Check if already in cart
    const existingItem = cart.items.find(item => item.serviceId === parseInt(service.id));
    if (existingItem) {
        showError('Serviço já está no carrinho');
        return;
    }
    
    // Add to cart
    const newItem = {
        serviceId: parseInt(service.id),
        serviceName: service.nome,
        serviceType: service.tipo,
        price: parseFloat(service.preco),
        quantity: 1,
        selectedDate: null,
        selectedDateId: null,
        datas: []
    };
    
    cart.items.push(newItem);
    saveCart();
    syncCartWithSession();
    updateCartDisplay();
    updateCartCount();
    
    showSuccess(`${service.nome} adicionado ao carrinho!`);
}

/**
 * Handle adding service to cart
 */
function handleAddToCart(e) {
    e.preventDefault();
    
    const serviceId = parseInt($(this).data('service-id'));
    const serviceName = $(this).data('service-name');
    const serviceType = $(this).data('service-type');
    const servicePrice = parseFloat($(this).data('service-price'));
    
    // Check cart limit
    if (cart.items.length >= 5) {
        showError('Carrinho pode ter no máximo 5 serviços');
        return;
    }
    
    // Check if already in cart
    const existingItem = cart.items.find(item => item.serviceId === serviceId);
    if (existingItem) {
        showError('Serviço já está no carrinho');
        return;
    }
    
    // Add to cart
    const newItem = {
        serviceId: serviceId,
        serviceName: serviceName,
        serviceType: serviceType,
        price: servicePrice,
        quantity: 1,
        selectedDate: null,
        selectedDateId: null,
        datas: []
    };
    
    cart.items.push(newItem);
    cart.total = cart.items.reduce((sum, item) => sum + item.price, 0);
    
    saveCart();
    
    // Update button state
    $(this).prop('disabled', true).text('Adicionado ao Carrinho');
    
    showSuccess('Serviço adicionado ao carrinho!');
    
    // Show date selection modal
    showDateSelectionModal(serviceId, serviceName);
}

/**
 * Remove service from cart
 */
function handleRemoveFromCart(e) {
    e.preventDefault();
    
    const serviceId = parseInt($(this).data('service-id'));
    cart.items = cart.items.filter(item => item.serviceId !== serviceId);
    cart.total = cart.items.reduce((sum, item) => sum + item.price, 0);
    
    saveCart();
    
    // Re-enable add to cart button if on search page
    $(`.add-to-cart[data-service-id="${serviceId}"]`)
        .prop('disabled', false)
        .text('Adicionar ao Carrinho');
    
    // Remove from cart display
    $(`#cart-item-${serviceId}`).fadeOut(function() {
        $(this).remove();
        if (cart.items.length === 0) {
            showEmptyCart();
        }
    });
}

/**
 * Handle date selection
 */
function handleSelectDate(e) {
    e.preventDefault();
    
    const button = $(this);
    const dateId = button.data('date-id');
    const dateValue = button.data('date-value');
    const serviceId = parseInt(button.data('service-id'));
    
    // Update cart item
    const cartItem = cart.items.find(item => item.serviceId === serviceId);
    if (cartItem) {
        cartItem.selectedDate = dateValue;
        cartItem.selectedDateId = dateId;
        saveCart();
        
        // Update UI
        button.closest('.modal').modal('hide');
        showSuccess('Data selecionada com sucesso');
        
        // Update cart display
        updateCartDisplay();
    }
}

/**
 * Show date selection modal
 */
function showDateSelectionModal(serviceId, serviceName) {
    // Determinar o caminho correto baseado na URL atual
    const currentPath = window.location.pathname;
    let getDateUrl;
    
    if (currentPath.includes('/admin/')) {
        // Estamos na área admin
        getDateUrl = 'get_dates.php';
    } else {
        // Estamos na área pública
        getDateUrl = 'get_dates.php';
    }
    
    // Load available dates via AJAX
    $.ajax({
        url: getDateUrl,
        type: 'GET',
        data: { service_id: serviceId },
        dataType: 'json',
        success: function(dates) {
            displayDateModal(serviceId, serviceName, dates);
        },
        error: function() {
            showError('Erro ao carregar datas disponíveis');
        }
    });
}

/**
 * Display date selection modal
 */
function displayDateModal(serviceId, serviceName, dates) {
    let modalHtml = `
        <div class="modal fade" id="dateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selecionar Data - ${escapeHtml(serviceName)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Escolha uma data disponível para este serviço:</p>
                        <div class="available-dates">
    `;
    
    if (dates.length > 0) {
        dates.forEach(date => {
            modalHtml += `
                <div class="date-option mb-2">
                    <button type="button" class="btn btn-outline-primary w-100 select-date" 
                            data-service-id="${serviceId}" 
                            data-date-id="${date.id}" 
                            data-date-value="${date.data}">
                        ${formatDate(date.data)}
                    </button>
                </div>
            `;
        });
    } else {
        modalHtml += `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Nenhuma data disponível para este serviço no momento.
            </div>
        `;
    }
    
    modalHtml += `
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    $('#dateModal').remove();
    
    // Add new modal
    $('body').append(modalHtml);
    
    // Show modal
    new bootstrap.Modal(document.getElementById('dateModal')).show();
}

/**
 * Update cart display
 */
function updateCartDisplay() {
    const cartCount = cart.items.length;
    const cartTotal = cart.total;
    
    // Update cart badge
    $('.cart-badge').text(cartCount);
    
    // Update cart total
    $('.cart-total').text(formatCurrency(cartTotal));
    
    // Show/hide cart button
    if (cartCount > 0) {
        $('.cart-button').show();
    } else {
        $('.cart-button').hide();
    }
    
    // Update buttons on search page
    cart.items.forEach(item => {
        $(`.add-to-cart[data-service-id="${item.serviceId}"]`)
            .prop('disabled', true)
            .text('No Carrinho');
    });
}

/**
 * Display cart items on cart page
 */
function displayCartItems() {
    const container = $('#cart-items');
    
    if (cart.items.length === 0) {
        showEmptyCart();
        return;
    }
    
    container.empty();
    
    cart.items.forEach(item => {
        const itemHtml = `
            <div class="card mb-3" id="cart-item-${item.serviceId}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">${escapeHtml(item.serviceName)}</h5>
                            <small class="text-muted">${escapeHtml(item.serviceType)}</small>
                        </div>
                        <div class="col-md-2">
                            <strong>R$ ${formatCurrency(item.price)}</strong>
                        </div>
                        <div class="col-md-3">
                            ${item.selectedDate ? 
                                `<small class="text-success">
                                    <i class="bi bi-calendar-check"></i> 
                                    ${formatDate(item.selectedDate)}
                                </small>` : 
                                `<button type="button" class="btn btn-sm btn-outline-primary" 
                                         onclick="showDateSelectionModal(${item.serviceId}, '${escapeHtml(item.serviceName)}')">
                                    <i class="bi bi-calendar"></i> Selecionar Data
                                </button>
                                <small class="text-warning d-block">
                                    Data não selecionada
                                </small>`
                            }
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-from-cart" 
                                    data-service-id="${item.serviceId}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(itemHtml);
    });
    
    // Show/hide checkout button
    const allDatesSelected = cart.items.every(item => item.selectedDate !== null);
    $('#proceedToCheckout').prop('disabled', !allDatesSelected);
    
    if (!allDatesSelected) {
        $('#checkout-warning').show();
    } else {
        $('#checkout-warning').hide();
    }
}

/**
 * Show empty cart message
 */
function showEmptyCart() {
    $('#cart-items').html(`
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">Carrinho vazio</h4>
            <p class="text-muted">Adicione alguns serviços para continuar</p>
            <a href="buscar.php" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar Serviços
            </a>
        </div>
    `);
    
    $('#proceedToCheckout').prop('disabled', true);
    $('#checkout-warning').hide();
}

/**
 * Clear entire cart
 */
function clearCart() {
    if (confirm('Deseja realmente limpar todo o carrinho?')) {
        cart = { items: [], total: 0 };
        saveCart();
        
        // Re-enable all add to cart buttons
        $('.add-to-cart').prop('disabled', false).text('Adicionar ao Carrinho');
        
        if (window.location.pathname.includes('carrinho.php')) {
            showEmptyCart();
        }
        
        showSuccess('Carrinho limpo com sucesso');
    }
}

/**
 * Proceed to checkout
 */
function proceedToCheckout() {
    if (cart.items.length === 0) {
        showError('Carrinho está vazio');
        return;
    }
    
    const allDatesSelected = cart.items.every(item => item.selectedDate !== null);
    if (!allDatesSelected) {
        showError('Selecione datas para todos os serviços');
        return;
    }
    
    // Save cart and redirect to public checkout
    saveCart();
    
    // Verificar se estamos na página pública ou admin
    const currentPath = window.location.pathname;
    if (currentPath.includes('/public/')) {
        window.location.href = 'checkout.php';
    } else {
        window.location.href = '../public/checkout.php';
    }
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    try {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
    } catch (e) {
        return dateString;
    }
}

/**
 * Format currency for display
 */
function formatCurrency(value) {
    try {
        return parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    } catch (e) {
        return '0,00';
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
