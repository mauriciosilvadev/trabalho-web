<?php

/**
 * Página Pública - Finalização de Compra
 * Permite que clientes finalizem a contratação dos serviços
 */

session_start();

// Verificar se cliente está logado
if (!isset($_SESSION['client_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once '../shared/dao/ServicoDAO.php';
require_once '../shared/dao/ContratacaoDAO.php';
require_once '../shared/dao/DataDisponivelDAO.php';

// Verificar se existe carrinho
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: buscar.php');
    exit;
}

$error = '';
$success = '';

// Processar finalização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $contratacaoDAO = new ContratacaoDAO();
        $servicoDAO = new ServicoDAO();

        // Processar finalização usando o método create adequado
        $totalGeral = 0;

        // Primeiro calcular total
        foreach ($_SESSION['cart'] as $cartItem) {
            if (!isset($cartItem['data_contratacao'])) {
                $serviceName = isset($cartItem['nome']) ? $cartItem['nome'] : (isset($cartItem['serviceName']) ? $cartItem['serviceName'] : 'Serviço');
                throw new Exception('Selecione uma data para o serviço: ' . htmlspecialchars($serviceName));
            }

            // Buscar serviço para verificar preço atual
            $serviceId = isset($cartItem['id']) ? $cartItem['id'] : (isset($cartItem['serviceId']) ? $cartItem['serviceId'] : null);

            if (!$serviceId) {
                throw new Exception('ID do serviço não encontrado');
            }

            $servico = $servicoDAO->buscarPorId($serviceId);
            if (!$servico) {
                throw new Exception('Serviço não encontrado: ' . $serviceId);
            }

            $totalGeral += $servico['preco'];
        }

        // Criar contratação com cliente, itens e total
        $contratacaoId = $contratacaoDAO->create($_SESSION['client_id'], $_SESSION['cart'], $totalGeral);

        if (!$contratacaoId) {
            // Log adicional para debug
            error_log("Checkout failed - Client ID: " . $_SESSION['client_id'] . ", Cart: " . json_encode($_SESSION['cart']) . ", Total: " . $totalGeral);
            throw new Exception('Erro ao processar contratação. Verifique se todas as datas estão selecionadas.');
        }

        // Limpar carrinho após sucesso
        unset($_SESSION['cart']);

        $success = 'Contratação realizada com sucesso! Total: R$ ' . number_format($totalGeral, 2, ',', '.');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - Sistema de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../shared/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="buscar.php">
                <i class="bi bi-building"></i> Sistema de Serviços
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="meus_contratos.php">
                    <i class="bi bi-file-text"></i> Meus Contratos
                </a>
                <a class="nav-link" href="logout_cliente.php">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="buscar.php">Buscar Serviços</a></li>
                <li class="breadcrumb-item"><a href="carrinho.php">Carrinho</a></li>
                <li class="breadcrumb-item active">Finalizar Pedido</li>
            </ol>
        </nav>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle"></i> Sucesso!</h5>
                <p class="mb-2"><?= htmlspecialchars($success) ?></p>
                <hr>
                <div class="d-flex gap-2">
                    <a href="meus_contratos.php" class="btn btn-success">
                        <i class="bi bi-file-text"></i> Ver Meus Contratos
                    </a>
                    <a href="buscar.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Buscar Mais Serviços
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-triangle"></i> Erro</h5>
                <p class="mb-0"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <!-- Header -->
            <div class="row mb-4">
                <div class="col">
                    <h2><i class="bi bi-credit-card"></i> Finalizar Pedido</h2>
                    <p class="text-muted">Confirme os dados da sua contratação</p>
                </div>
            </div>

            <form method="POST" action="">
                <div class="row">
                    <!-- Dados do Cliente -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person"></i> Dados do Cliente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Nome:</label>
                                        <p class="fw-bold"><?= htmlspecialchars($_SESSION['client_name'] ?? 'Nome não informado') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email:</label>
                                        <p class="fw-bold"><?= htmlspecialchars($_SESSION['client_email'] ?? 'Email não informado') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo dos Serviços -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-task"></i> Serviços Contratados
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="checkout-items">
                                    <!-- Items will be loaded by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo Financeiro -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-calculator"></i> Resumo do Pedido
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="cart-total">R$ 0,00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Desconto:</span>
                                    <span class="text-success">R$ 0,00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="cart-total text-success fs-5">R$ 0,00</strong>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg" id="finalizeButton">
                                        <i class="bi bi-check-circle"></i> Confirmar Pedido
                                    </button>
                                    <a href="carrinho.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar ao Carrinho
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Termos -->
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-shield-check"></i> Termos e Condições</h6>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                                    <label class="form-check-label small" for="acceptTerms">
                                        Aceito os termos e condições de uso
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">
                                    Ao finalizar o pedido, você concorda com nossos termos de serviço e política de privacidade.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../admin/contratacao/carrinho.js"></script>
    <script>
        $(document).ready(function() {
            // Se checkout foi bem-sucedido, limpar carrinho do localStorage
            <?php if ($success): ?>
                // Usar função clearCartSilent() se disponível (sem confirmação)
                if (typeof clearCartSilent === 'function') {
                    clearCartSilent();
                } else {
                    // Fallback manual
                    localStorage.removeItem('service_cart');
                    if (typeof cart !== 'undefined') {
                        cart = {
                            items: [],
                            total: 0
                        };
                    }
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                }
            <?php else: ?>
                // Carregar itens do checkout
                loadCheckoutItems();

                // Atualizar total
                updateCartTotal();
            <?php endif; ?>

            // Verificar termos antes de finalizar
            $('#acceptTerms').change(function() {
                $('#finalizeButton').prop('disabled', !this.checked);
            });
        });

        function loadCheckoutItems() {
            const cart = getCart();
            const container = $('#checkout-items');

            if (cart.length === 0) {
                container.html('<p class="text-muted">Nenhum item no carrinho</p>');
                return;
            }

            let html = '';
            cart.forEach(function(item) {
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <h6 class="mb-1">${item.nome}</h6>
                            <p class="text-muted small mb-1">${item.tipo}</p>
                            ${item.data_contratacao ? 
                                `<p class="text-success small mb-0"><i class="bi bi-calendar-check"></i> ${formatDate(item.data_contratacao)}</p>` : 
                                `<p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle"></i> Data não selecionada</p>`
                            }
                        </div>
                        <div class="text-end">
                            <span class="fw-bold">R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')}</span>
                        </div>
                    </div>
                `;
            });

            container.html(html);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR');
        }
    </script>
</body>

</html>