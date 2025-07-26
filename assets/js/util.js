/**
 * Utility functions for Service Management System
 */

// Document ready function
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize modals
    initializeModals();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize cart functionality
    initializeCart();
});

/**
 * Initialize modals
 */
function initializeModals() {
    // Auto-hide alerts after 5 seconds
    $('.alert[data-auto-dismiss]').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut();
        }, 5000);
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // CPF validation
    $('input[name="cpf"]').on('input', function() {
        var cpf = $(this).val().replace(/\D/g, '');
        if (cpf.length === 11) {
            if (!isValidCPF(cpf)) {
                $(this).addClass('is-invalid');
                showFieldError($(this), 'CPF inválido');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                hideFieldError($(this));
            }
        }
    });
    
    // Email validation
    $('input[type="email"]').on('blur', function() {
        var email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass('is-invalid');
            showFieldError($(this), 'Email inválido');
        } else {
            $(this).removeClass('is-invalid');
            hideFieldError($(this));
        }
    });
    
    // Price validation
    $('input[name="preco"]').on('input', function() {
        var value = $(this).val();
        if (value && (isNaN(value) || parseFloat(value) <= 0)) {
            $(this).addClass('is-invalid');
            showFieldError($(this), 'Preço deve ser um número positivo');
        } else {
            $(this).removeClass('is-invalid');
            hideFieldError($(this));
        }
    });
}

/**
 * Initialize cart functionality
 */
function initializeCart() {
    updateCartDisplay();
}

/**
 * Show loading state
 */
function showLoading(element) {
    if (typeof element === 'string') {
        element = $(element);
    }
    element.find('.loading').addClass('show');
    element.find('button').prop('disabled', true);
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    if (typeof element === 'string') {
        element = $(element);
    }
    element.find('.loading').removeClass('show');
    element.find('button').prop('disabled', false);
}

/**
 * Show success message
 */
function showSuccess(message, container = '.container') {
    var alertHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss="true">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $(container).prepend(alertHtml);
}

/**
 * Show error message
 */
function showError(message, container = '.container') {
    var alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert" data-auto-dismiss="true">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $(container).prepend(alertHtml);
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.addClass('is-invalid');
    var errorDiv = field.next('.error-message');
    if (errorDiv.length === 0) {
        errorDiv = $('<div class="error-message"></div>');
        field.after(errorDiv);
    }
    errorDiv.text(message);
}

/**
 * Hide field error
 */
function hideFieldError(field) {
    field.removeClass('is-invalid');
    field.next('.error-message').remove();
}

/**
 * Confirm deletion
 */
function confirmDelete(message = 'Tem certeza que deseja excluir este item?') {
    return confirm(message);
}

/**
 * Format currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Format date
 */
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

/**
 * Validate CPF
 */
function isValidCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    var digits = cpf.split('').map(Number);
    
    // Validate first digit
    var sum = 0;
    for (var i = 0; i < 9; i++) {
        sum += digits[i] * (10 - i);
    }
    var firstDigit = 11 - (sum % 11);
    if (firstDigit >= 10) firstDigit = 0;
    
    if (digits[9] !== firstDigit) return false;
    
    // Validate second digit
    sum = 0;
    for (var i = 0; i < 10; i++) {
        sum += digits[i] * (11 - i);
    }
    var secondDigit = 11 - (sum % 11);
    if (secondDigit >= 10) secondDigit = 0;
    
    return digits[10] === secondDigit;
}

/**
 * Validate email
 */
function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Get cart from sessionStorage
 */
function getCart() {
    var cart = sessionStorage.getItem('cart');
    return cart ? JSON.parse(cart) : [];
}

/**
 * Save cart to sessionStorage
 */
function saveCart(cart) {
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

/**
 * Add item to cart
 */
function addToCart(servico) {
    var cart = getCart();
    
    // Check if cart is full (max 5 items)
    if (cart.length >= 5) {
        showError('Carrinho pode ter no máximo 5 serviços');
        return false;
    }
    
    // Check if service is already in cart
    var existingItem = cart.find(item => item.servico_id === servico.id);
    if (existingItem) {
        showError('Serviço já está no carrinho');
        return false;
    }
    
    // Add to cart
    cart.push({
        servico_id: servico.id,
        servico_nome: servico.nome,
        servico_tipo: servico.tipo,
        preco: servico.preco,
        data_id: null,
        data_servico: null
    });
    
    saveCart(cart);
    showSuccess('Serviço adicionado ao carrinho');
    return true;
}

/**
 * Remove item from cart
 */
function removeFromCart(servicoId) {
    var cart = getCart();
    cart = cart.filter(item => item.servico_id !== servicoId);
    saveCart(cart);
    
    // Reload cart page if we're on it
    if (window.location.pathname.includes('carrinho')) {
        location.reload();
    }
}

/**
 * Update cart display
 */
function updateCartDisplay() {
    var cart = getCart();
    var cartCount = cart.length;
    var cartTotal = cart.reduce((total, item) => total + parseFloat(item.preco || 0), 0);
    
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
}

/**
 * Clear cart
 */
function clearCart() {
    sessionStorage.removeItem('cart');
    updateCartDisplay();
}

/**
 * AJAX helper function
 */
function makeAjaxRequest(url, data, method = 'POST') {
    return $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        beforeSend: function() {
            showLoading('body');
        },
        complete: function() {
            hideLoading('body');
        },
        error: function(xhr, status, error) {
            showError('Erro na requisição: ' + error);
        }
    });
}
