<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ContratacaoDAO.php';

Auth::requireAuth();

// Get financial statistics
$contratacaoDAO = new ContratacaoDAO();

$estatisticas = $contratacaoDAO->getStatistics();
$user = Auth::getUser();

// Get monthly revenue data
$receita_mensal = $contratacaoDAO->getMonthlyRevenue();
$receita_anual = $contratacaoDAO->getYearlyRevenue();
$contratos_por_mes = $contratacaoDAO->getContractsByMonth();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios Financeiros - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link" href="../contratacao/listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="financeiro.php">
                            <i class="bi bi-graph-up"></i> Relatórios Financeiros
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
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nome']) ?>
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
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-graph-up text-primary"></i> Relatórios Financeiros
                </h1>
                <p class="text-muted">Análise detalhada da receita e performance financeira</p>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="#detalhamento-financeiro" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                            <div class="stats-number">R$ <?= number_format($estatisticas['receita_total'] ?? 0, 2, ',', '.') ?></div>
                            <div class="stats-label">Receita Total</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver detalhamento financeiro</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../contratacao/listar.php?status=confirmada&periodo=mes" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                            <div class="stats-number"><?= $estatisticas['contratos_mes'] ?? 0 ?></div>
                            <div class="stats-label">Contratos este Mês</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver contratos do mês</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="#grafico-receita" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
                            <div class="stats-number">R$ <?= number_format($receita_mensal ?? 0, 2, ',', '.') ?></div>
                            <div class="stats-label">Receita do Mês</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver gráfico mensal</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../contratacao/listar.php" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up-arrow" style="font-size: 2rem;"></i>
                            <div class="stats-number"><?= $estatisticas['total_contratos'] ?? 0 ?></div>
                            <div class="stats-label">Total de Contratos</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Gerenciar contratos</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card" id="grafico-receita">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i> Receita Mensal
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart"></i> Distribuição de Contratos
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="contractsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Financial Table -->
        <div class="row">
            <div class="col-12">
                <div class="card" id="detalhamento-financeiro">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i> Detalhamento Financeiro
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Contratos</th>
                                        <th>Receita Total</th>
                                        <th>Receita Média</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Este Mês</strong></td>
                                        <td><?= $estatisticas['contratos_mes'] ?? 0 ?></td>
                                        <td>R$ <?= number_format($receita_mensal ?? 0, 2, ',', '.') ?></td>
                                        <td>R$ <?= $estatisticas['contratos_mes'] > 0 ? number_format(($receita_mensal ?? 0) / ($estatisticas['contratos_mes'] ?? 1), 2, ',', '.') : '0,00' ?></td>
                                        <td><span class="badge bg-success">Ativo</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Este Ano</strong></td>
                                        <td><?= $estatisticas['total_contratos'] ?? 0 ?></td>
                                        <td>R$ <?= number_format($receita_anual ?? 0, 2, ',', '.') ?></td>
                                        <td>R$ <?= $estatisticas['total_contratos'] > 0 ? number_format(($receita_anual ?? 0) / ($estatisticas['total_contratos'] ?? 1), 2, ',', '.') : '0,00' ?></td>
                                        <td><span class="badge bg-info">Anual</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Geral</strong></td>
                                        <td><?= $estatisticas['total_contratos'] ?? 0 ?></td>
                                        <td>R$ <?= number_format($estatisticas['receita_total'] ?? 0, 2, ',', '.') ?></td>
                                        <td>R$ <?= $estatisticas['total_contratos'] > 0 ? number_format(($estatisticas['receita_total'] ?? 0) / ($estatisticas['total_contratos'] ?? 1), 2, ',', '.') : '0,00' ?></td>
                                        <td><span class="badge bg-primary">Geral</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-download"></i> Exportar Relatórios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="exportar.php?tipo=pdf&periodo=mensal" class="btn btn-danger w-100 mb-2">
                                    <i class="bi bi-file-pdf"></i> PDF Mensal
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="exportar.php?tipo=excel&periodo=mensal" class="btn btn-success w-100 mb-2">
                                    <i class="bi bi-file-excel"></i> Excel Mensal
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="exportar.php?tipo=pdf&periodo=anual" class="btn btn-danger w-100 mb-2">
                                    <i class="bi bi-file-pdf"></i> PDF Anual
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="exportar.php?tipo=excel&periodo=anual" class="btn btn-success w-100 mb-2">
                                    <i class="bi bi-file-excel"></i> Excel Anual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../shared/assets/js/util.js"></script>
    
    <script>
        // Smooth scrolling for internal links
        $(document).ready(function() {
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                }
            });
        });
    </script>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Receita Mensal (R$)',
                    data: [<?= implode(',', array_fill(0, 12, $receita_mensal ?? 0)) ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Contracts Chart
        const contractsCtx = document.getElementById('contractsChart').getContext('2d');
        new Chart(contractsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Este Mês', 'Meses Anteriores'],
                datasets: [{
                    data: [<?= $estatisticas['contratos_mes'] ?? 0 ?>, <?= ($estatisticas['total_contratos'] ?? 0) - ($estatisticas['contratos_mes'] ?? 0) ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html> 