<?php

/**
 * Página Inicial Pública
 * Página de entrada do site com busca de serviços
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../shared/dao/ServicoDAO.php';

$servicoDAO = new ServicoDAO();

// Buscar serviços em destaque (últimos 6)
$servicosDestaque = array_slice($servicoDAO->findAll(), 0, 6);

// Buscar tipos de serviços
$tipos = $servicoDAO->getTypes();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../shared/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Sistema de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buscar.php">
                            <i class="bi bi-search"></i> Buscar Serviços
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <!-- Carrinho sempre visível no canto direito -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="carrinho.php">
                            <i class="bi bi-cart"></i> Carrinho
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display: none;">
                                0
                            </span>
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

    <!-- Hero Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-primary mb-3">
                        Encontre os Melhores Serviços
                    </h1>
                    <p class="lead text-muted mb-4">
                        Conectamos você aos melhores prestadores de serviços da sua região.
                        Contrate com segurança e qualidade garantida.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="buscar.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Buscar Serviços
                        </a>
                        <a href="cadastro.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-person-plus"></i> Criar Conta
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-building display-1 text-primary"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Busca Rápida -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">
                                <i class="bi bi-search"></i> Busca Rápida
                            </h3>
                            <form action="buscar.php" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control form-control-lg"
                                            name="nome" placeholder="O que você procura?">
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select form-select-lg" name="tipo">
                                            <option value="">Todos os tipos</option>
                                            <?php foreach ($tipos as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo) ?>">
                                                    <?= htmlspecialchars($tipo) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços em Destaque -->
    <?php if (!empty($servicosDestaque)): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5">
                    <i class="bi bi-star"></i> Serviços em Destaque
                </h2>
                <div class="row">
                    <?php foreach ($servicosDestaque as $servico): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title text-primary">
                                            <?= htmlspecialchars($servico['nome']) ?>
                                        </h5>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($servico['tipo']) ?>
                                        </span>
                                    </div>
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-success mb-0">
                                            R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                        </span>
                                        <button class="btn btn-outline-primary"
                                            onclick="addToCartFromHome(<?= $servico['id'] ?>)">
                                            <i class="bi bi-cart-plus"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="buscar.php" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Ver Todos os Serviços
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Categorias -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="bi bi-grid"></i> Categorias de Serviços
            </h2>
            <div class="row">
                <?php
                $iconMap = [
                    'Limpeza' => 'bi-brush',
                    'Manutenção' => 'bi-tools',
                    'Consultoria' => 'bi-lightbulb',
                    'Tecnologia' => 'bi-laptop',
                    'Educação' => 'bi-book',
                    'Saúde' => 'bi-heart-pulse'
                ];
                foreach ($tipos as $tipo):
                    $icon = $iconMap[$tipo] ?? 'bi-gear';
                ?>
                    <div class="col-lg-2 col-md-4 col-6 mb-4">
                        <a href="buscar.php?tipo=<?= urlencode($tipo) ?>"
                            class="text-decoration-none">
                            <div class="card text-center h-100 shadow-sm hover-card">
                                <div class="card-body">
                                    <i class="bi <?= $icon ?> display-4 text-primary mb-3"></i>
                                    <h6 class="card-title"><?= htmlspecialchars($tipo) ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-building"></i> Sistema de Serviços</h5>
                    <p class="text-muted">Conectando clientes aos melhores prestadores de serviços.</p>
                </div>
                <div class="col-md-6 text-end">
                    <h6>Links Úteis</h6>
                    <div class="d-flex justify-content-end gap-3">
                        <a href="buscar.php" class="text-white-50">Buscar</a>
                        <a href="cadastro.php" class="text-white-50">Cadastrar</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../admin/contratacao/carrinho.js"></script>
    <script>
        // Função para adicionar ao carrinho da página inicial
        function addToCartFromHome(servicoId) {
            // Buscar dados do serviço e mostrar modal de seleção de data
            $.get('buscar.php', {
                ajax: 1,
                id: servicoId
            }, function(servico) {
                if (servico) {
                    // Em vez de adicionar diretamente, mostrar modal de seleção de data
                    showDateSelectionModal(servico.id, servico.nome, servico.tipo, servico.preco);
                } else {
                    showError('Erro ao carregar dados do serviço.');
                }
            }).fail(function() {
                showError('Erro ao carregar dados do serviço.');
            });
        }

        // Atualizar contador do carrinho
        $(document).ready(function() {
            // Show cart if user is logged in
            <?php if (isset($_SESSION['client_id'])): ?>
                $('.cart-count').show().parent().show();
            <?php endif; ?>
            updateCartCount();
        });

        function showSuccess(message) {
            // Implementar toast ou alert para feedback
            alert(message);
        }

        function showError(message) {
            // Implementar toast ou alert para feedback
            alert(message);
        }
    </script>

    <style>
        .hover-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
</body>

</html>