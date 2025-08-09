<?php

/**
 * Página Pública - Carrinho de Compras
 * Permite que clientes vejam e gerenciem seu carrinho
 */

session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Sistema de Serviços</title>
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

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="buscar.php">
                            <i class="bi bi-search"></i> Buscar Serviços
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <!-- Carrinho sempre visível no canto direito -->
                    <li class="nav-item">
                        <a class="nav-link active" href="carrinho.php">
                            <i class="bi bi-cart"></i> Carrinho
                        </a>
                    </li>

                    <?php if (isset($_SESSION['client_id'])): ?>
                        <!-- Cliente logado -->
                        <li class="nav-item">
                            <a class="nav-link" href="meus_contratos.php">
                                <i class="bi bi-file-text"></i> Meus Contratos
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['client_name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout_cliente.php">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Cliente não logado -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">
                                <i class="bi bi-person-plus"></i> Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="buscar.php">Buscar Serviços</a></li>
                <li class="breadcrumb-item active">Carrinho</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-cart"></i> Meu Carrinho</h2>
                <p class="text-muted">Revise os serviços selecionados antes de finalizar</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="buscar.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Continuar Comprando
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-task"></i> Serviços Selecionados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-items">
                            <!-- Items will be loaded by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator"></i> Resumo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="cart-total">R$ 0,00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="cart-total text-success">R$ 0,00</strong>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-success" id="proceedToCheckout">
                                <i class="bi bi-arrow-right"></i> Finalizar Pedido
                            </a>
                            <button type="button" class="btn btn-outline-danger" id="clearCart">
                                <i class="bi bi-trash"></i> Limpar Carrinho
                            </button>
                        </div>

                        <div class="alert alert-warning mt-3" id="checkout-warning" style="display: none;">
                            <small><i class="bi bi-exclamation-triangle"></i> Selecione datas para todos os serviços antes de finalizar.</small>
                        </div>
                    </div>
                </div>

                <!-- Cart Info -->
                <div class="card">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Informações</h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li>• Máximo 5 serviços por pedido</li>
                            <li>• Selecione datas disponíveis</li>
                            <li>• Produtos sujeitos à disponibilidade</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast para mensagens -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Sistema</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../admin/contratacao/carrinho.js"></script>
    <script>
        // Função para mostrar mensagens
        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showError(message) {
            showToast(message, 'error');
        }

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastBody = toast.querySelector('.toast-body');

            toastBody.textContent = message;
            toast.className = 'toast ' + (type === 'success' ? 'bg-success text-white' : 'bg-danger text-white');

            new bootstrap.Toast(toast).show();
        }
    </script>
</body>

</html>