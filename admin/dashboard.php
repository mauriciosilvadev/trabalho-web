<?php
require_once '../shared/config/auth.php';
require_once '../shared/dao/ClienteDAO.php';
require_once '../shared/dao/ServicoDAO.php';
require_once '../shared/dao/ContratacaoDAO.php';

Auth::requireAuth();

// Get statistics
$clienteDAO = new ClienteDAO();
$servicoDAO = new ServicoDAO();
$contratacaoDAO = new ContratacaoDAO();

$totalClientes = count($clienteDAO->findAll());
$totalServicos = count($servicoDAO->findAll());
$totalContratos = count($contratacaoDAO->findAll());
$estatisticas = $contratacaoDAO->getStatistics();

$user = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../shared/assets/css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-gear-fill"></i> Sistema de Gestão de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="servicos/list.php">
                            <i class="bi bi-list-task"></i> Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes/list.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contratacao/listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios/financeiro.php">
                            <i class="bi bi-graph-up"></i> Relatórios Financeiros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios/list.php">
                            <i class="bi bi-person-gear"></i> Usuários
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
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
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Dashboard</h1>
                <p class="text-muted">Bem-vindo ao Sistema de Gestão de Serviços</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="clientes/list.php" class="text-decoration-none">
                    <div class="card stats-card dashboard-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <div class="stats-number"><?= $totalClientes ?></div>
                            <div class="stats-label">Clientes</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Clique para ver todos os clientes</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="servicos/list.php" class="text-decoration-none">
                    <div class="card stats-card dashboard-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-list-task" style="font-size: 2rem;"></i>
                            <div class="stats-number"><?= $totalServicos ?></div>
                            <div class="stats-label">Serviços</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Clique para ver todos os serviços</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="contratacao/listar.php" class="text-decoration-none">
                    <div class="card stats-card dashboard-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-check" style="font-size: 2rem;"></i>
                            <div class="stats-number"><?= $estatisticas['total_contratos'] ?? 0 ?></div>
                            <div class="stats-label">Contratos</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Clique para ver todos os contratos</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="relatorios/financeiro.php" class="text-decoration-none">
                    <div class="card stats-card dashboard-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                            <div class="stats-number">R$ <?= number_format($estatisticas['receita_total'] ?? 0, 2, ',', '.') ?></div>
                            <div class="stats-label">Receita Total</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Clique para ver relatórios financeiros</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>Ações Rápidas</h3>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-plus-circle text-primary" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Novo Serviço</h5>
                        <p class="card-text">Cadastrar um novo serviço no sistema</p>
                        <a href="servicos/form.php?from=dashboard" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Criar Serviço
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Novo Cliente</h5>
                        <p class="card-text">Cadastrar um novo cliente no sistema</p>
                        <a href="clientes/form.php?from=dashboard" class="btn btn-success">
                            <i class="bi bi-plus"></i> Criar Cliente
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text text-info" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Gerenciar Contratos</h5>
                        <p class="card-text">Visualizar e gerenciar contratos existentes</p>
                        <a href="contratacao/listar.php" class="btn btn-info">
                            <i class="bi bi-list-check"></i> Ver Contratos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($estatisticas['servicos_populares'])): ?>
            <div class="row">
                <div class="col-12">
                    <h3>Serviços Mais Contratados</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Serviço</th>
                                            <th>Total de Contratos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estatisticas['servicos_populares'] as $servico): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($servico['nome']) ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $servico['total'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Estatísticas do Mês</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Contratos este mês:</strong>
                                <span class="float-end badge bg-info"><?= $estatisticas['contratos_mes'] ?? 0 ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Total de Clientes:</strong>
                                <span class="float-end badge bg-success"><?= $totalClientes ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Total de Serviços:</strong>
                                <span class="float-end badge bg-warning"><?= $totalServicos ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../shared/assets/js/util.js"></script>
</body>

</html>
