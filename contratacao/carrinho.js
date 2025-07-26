/**
 * Cart management functionality
 */

// Cart state
let cart = {
    items: [],
    total: 0
};

// Initialize cart on page load
$(document).ready(function() {
    loadCart();
    updateCartDisplay();
    
    // Bind events
    $(document).on('click', '.add-to-cart', handleAddToCart);
    $(document).on('click', '.remove-from-cart', handleRemoveFromCart);
    $(document).on('click', '.select-date', handleSelectDate);
    $(document).on('click', '#clearCart', clearCart);
    $(document).on('click', '#proceedToCheckout', proceedToCheckout);
});

/**
 * Load cart from sessionStorage
 */
function loadCart() {
    const savedCart = sessionStorage.getItem('serviceCart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}

/**
 * Save cart to sessionStorage
 */
function saveCart() {
    sessionStorage.setItem('serviceCart', JSON.stringify(cart));
    updateCartDisplay();
}

/**
 * Add service to cart
 */
function handleAddToCart(e) {
    e.preventDefault();
    
    const button = $(this);
    const serviceId = button.data('service-id');
    const serviceName = button.data('service-name');
    const serviceType = button.data('service-type');
    const servicePrice = parseFloat(button.data('service-price'));
    
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
        selectedDate: null,
        selectedDateId: null
    };
    
    cart.items.push(newItem);
    cart.total = cart.items.reduce((sum, item) => sum + item.price, 0);
    
    saveCart();
    showSuccess('Serviço adicionado ao carrinho');
    
    // Disable button
    button.prop('disabled', true).text('No Carrinho');
    
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
    // Load available dates via AJAX
    $.ajax({
        url: 'get_dates.php',
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
    // Update cart badge
    $('.cart-badge').text(cart.items.length);
    
    // Update cart button visibility
    if (cart.items.length > 0) {
        $('.cart-button').show();
    } else {
        $('.cart-button').hide();
    }
    
    // Update cart total
    $('.cart-total').text(formatCurrency(cart.total));
    
    // Update cart items if on cart page
    if ($('#cart-items').length > 0) {
        displayCartItems();
    }
}

/**
 * Display cart items on cart page
 */
function displayCartItems() {
    const container = $('#cart-items');
    container.empty();
    
    if (cart.items.length === 0) {
        showEmptyCart();
        return;
    }
    
    cart.items.forEach(item => {
        const itemHtml = `
            <div class="cart-item" id="cart-item-${item.serviceId}">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5>${escapeHtml(item.serviceName)}</h5>
                        <small class="text-muted">${escapeHtml(item.serviceType)}</small>
                    </div>
                    <div class="col-md-3">
                        <strong>${formatCurrency(item.price)}</strong>
                    </div>
                    <div class="col-md-2">
                        ${item.selectedDate ? 
                            `<small class="text-success">
                                <i class="bi bi-calendar-check"></i> 
                                ${formatDate(item.selectedDate)}
                            </small>` : 
                            `<small class="text-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
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
            <i class="bi bi-cart" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3">Carrinho Vazio</h4>
            <p class="text-muted">Adicione alguns serviços ao seu carrinho</p>
            <a href="buscar.php" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar Serviços
            </a>
        </div>
    `);
    
    $('#cart-summary').hide();
}

/**
 * Clear entire cart
 */
function clearCart() {
    if (confirm('Tem certeza que deseja limpar o carrinho?')) {
        cart = { items: [], total: 0 };
        saveCart();
        
        // Re-enable all add to cart buttons
        $('.add-to-cart').prop('disabled', false).text('Adicionar ao Carrinho');
        
        showSuccess('Carrinho limpo com sucesso');
        
        if ($('#cart-items').length > 0) {
            showEmptyCart();
        }
    }
}

/**
 * Proceed to checkout
 */
function proceedToCheckout() {
    // Validate cart
    if (cart.items.length === 0) {
        showError('Carrinho está vazio');
        return;
    }
    
    const allDatesSelected = cart.items.every(item => item.selectedDate !== null);
    if (!allDatesSelected) {
        showError('Selecione datas para todos os serviços');
        return;
    }
    
    // Redirect to checkout
    window.location.href = 'resumo.php';
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('pt-BR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format currency for display
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
