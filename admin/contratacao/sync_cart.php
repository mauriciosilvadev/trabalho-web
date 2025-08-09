<?php

/**
 * Sincronização do carrinho com sessões PHP
 */
require_once '../../shared/config/auth.php';

Auth::requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $cartData = $_POST['cart'] ?? '';

    if (empty($cartData)) {
        // Clear cart session
        unset($_SESSION['cart']);
        echo json_encode(['success' => true, 'message' => 'Carrinho limpo']);
        exit;
    }

    $cart = json_decode($cartData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dados do carrinho inválidos');
    }

    // Validate cart structure
    if (!isset($cart['items']) || !is_array($cart['items'])) {
        throw new Exception('Estrutura do carrinho inválida');
    }

    // Convert cart format for PHP session
    $sessionCart = [];
    foreach ($cart['items'] as $item) {
        $sessionCart[] = [
            'servico_id' => $item['serviceId'] ?? null,
            'quantity' => $item['quantity'] ?? 1,
            'datas' => !empty($item['selectedDate']) ? [$item['selectedDate']] : []
        ];
    }

    // Save to session
    $_SESSION['cart'] = $sessionCart;

    echo json_encode([
        'success' => true,
        'message' => 'Carrinho sincronizado',
        'items_count' => count($sessionCart)
    ]);
} catch (Exception $e) {
    error_log("Cart sync error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
