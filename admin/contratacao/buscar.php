<?php
require_once '../config/auth.php';
require_once '../dao/ServicoDAO.php';
require_once '../dao/DataDisponivelDAO.php';

Auth::requireAuth();

$servicoDAO = new ServicoDAO();
$dataDAO = new DataDisponivelDAO();
$user = Auth::getUser();

$servicos = [];
$filters = [];
$tipos = $servicoDAO->getTypes();

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $filters = [
        'id' => trim($_GET['id'] ?? ''),
        'nome' => trim($_GET['nome'] ?? ''),
        'tipo' => trim($_GET['tipo'] ?? ''),
        'preco_min' => trim($_GET['preco_min'] ?? ''),
        'preco_max' => trim($_GET['preco_max'] ?? ''),
        'data_disponivel' => trim($_GET['data_disponivel'] ?? '')
    ];

    $servicos = $servicoDAO->search($filters);
} else {
    // Show all services with available dates by default
    $servicos = $servicoDAO->findWithAvailableDates();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Serviços - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <a class="nav-link active" href="buscar.php">
                            <i class="bi bi-search"></i> Buscar/Contratar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../usuarios/list.php">
                            <i class="bi bi-person-gear"></i> Usuários
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link cart-button" href="carrinho.php" style="display: none;">
                            <i class="bi bi-cart"></i> Carrinho
                            <span class="badge bg-light text-dark cart-badge">0</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nome']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Buscar e Contratar Serviços</h1>
                <p class="text-muted">Encontre o serviço ideal e adicione ao seu carrinho</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="carrinho.php" class="btn btn-outline-primary cart-button" style="display: none;">
                    <i class="bi bi-cart"></i> Ver Carrinho (<span class="cart-badge">0</span>)
                </a>
            </div>
        </div>

        <!-- Search Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-funnel"></i> Filtros de Busca
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="id" class="form-label">ID</label>
                                <input type="number" class="form-control" id="id" name="id"
                                    value="<?= htmlspecialchars($filters['id'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="<?= htmlspecialchars($filters['nome'] ?? '') ?>"
                                    placeholder="Nome do serviço">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= htmlspecialchars($tipo) ?>"
                                            <?= ($filters['tipo'] ?? '') === $tipo ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="preco_min" class="form-label">Preço Min</label>
                                <input type="number" class="form-control" id="preco_min" name="preco_min"
                                    value="<?= htmlspecialchars($filters['preco_min'] ?? '') ?>"
                                    min="0" step="0.01" placeholder="0,00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="preco_max" class="form-label">Preço Max</label>
                                <input type="number" class="form-control" id="preco_max" name="preco_max"
                                    value="<?= htmlspecialchars($filters['preco_max'] ?? '') ?>"
                                    min="0" step="0.01" placeholder="9999,99">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="data_disponivel" class="form-label">Data Disponível</label>
                                <input type="date" class="form-control" id="data_disponivel" name="data_disponivel"
                                    value="<?= htmlspecialchars($filters['data_disponivel'] ?? '') ?>"
                                    min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                    <a href="buscar.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Limpar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <div class="row">
            <div class="col-12">
                <h3>Serviços Disponíveis</h3>
                <p class="text-muted"><?= count($servicos) ?> serviço(s) encontrado(s)</p>
            </div>
        </div>

        <?php if (empty($servicos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Nenhum serviço encontrado</h4>
                <p class="text-muted">Tente ajustar os filtros de busca</p>
                <a href="buscar.php" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Ver Todos os Serviços
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($servicos as $servico): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card service-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="service-type"><?= htmlspecialchars($servico['tipo']) ?></span>
                                <small class="text-muted">ID: <?= $servico['id'] ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($servico['nome']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($servico['descricao']) ?></p>
                                <div class="service-price mb-2">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></div>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-check"></i>
                                        <?= ($servico['datas_disponiveis'] ?? 0) ?> data(s) disponível(is)
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary w-100 add-to-cart"
                                    data-service-id="<?= $servico['id'] ?>"
                                    data-service-name="<?= htmlspecialchars($servico['nome']) ?>"
                                    data-service-type="<?= htmlspecialchars($servico['tipo']) ?>"
                                    data-service-price="<?= $servico['preco'] ?>">
                                    <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script src="carrinho.js"></script>
</body>

</html>
