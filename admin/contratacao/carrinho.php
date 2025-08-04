<?php
require_once '../../shared/config/auth.php';

Auth::requireAuth();

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: buscar.php');
    exit;
}

$cart = $_SESSION['cart'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/assets/css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-gear-fill"></i> Sistema de Gestão de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../servicos/list.php">
                            <i class="bi bi-list-task"></i> Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../clientes/list.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../relatorios/financeiro.php">
                            Relatórios Financeiros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../usuarios/list.php">
                            <i class="bi bi-person-gear"></i> Usuários
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars(Auth::getUser()['nome']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
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
                <h1>Carrinho de Compras</h1>
                <p class="text-muted">Revise os serviços selecionados e suas datas</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="buscar.php" class="btn btn-outline-primary">
                    <i class="bi bi-plus"></i> Adicionar Mais Serviços
                </a>
            </div>
        </div>

        <!-- Cart Content -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cart"></i> Itens no Carrinho
                        </h5>
                    </div>
                    <div class="card-body" id="cart-items">
                        <!-- Cart items will be loaded here by JavaScript -->
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Cart Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator"></i> Resumo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Itens:</span>
                            <span id="cart-count">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="cart-total">R$ 0,00</strong>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" id="proceedToCheckout" disabled>
                                <i class="bi bi-arrow-right"></i> Finalizar Pedido
                            </button>
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
                            <li>• Dados salvos automaticamente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../shared/assets/js/util.js"></script>
    <script src="carrinho.js"></script>
    <script>
        $(document).ready(function() {
            displayCartItems();
        });
    </script>
</body>

</html>
