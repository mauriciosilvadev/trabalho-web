<?php

/**
 * API Pública - Sincronizar Carrinho
 * Permite sincronização do carrinho sem autenticação admin
 */

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $cartData = $_POST['cart'] ?? '';

    if (empty($cartData)) {
        echo json_encode(['success' => false, 'message' => 'Dados do carrinho não fornecidos']);
        exit;
    }

    $cart = json_decode($cartData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Dados do carrinho inválidos']);
        exit;
    }

    // Converter formato JavaScript para PHP
    $cartItems = [];
    if (isset($cart['items']) && is_array($cart['items'])) {
        foreach ($cart['items'] as $item) {
            $cartItems[] = [
                'id' => $item['serviceId'] ?? null,
                'nome' => $item['serviceName'] ?? '',
                'tipo' => $item['serviceType'] ?? '',
                'preco' => $item['price'] ?? 0,
                'data_contratacao' => $item['selectedDate'] ?? null,
                'data_id' => $item['selectedDateId'] ?? null
            ];
        }
    }

    // Salvar carrinho na sessão no formato PHP
    $_SESSION['cart'] = $cartItems;

    echo json_encode(['success' => true, 'message' => 'Carrinho sincronizado com sucesso']);
} catch (Exception $e) {
    error_log("Cart sync error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
