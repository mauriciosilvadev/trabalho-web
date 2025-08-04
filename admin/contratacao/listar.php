<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ContratacaoDAO.php';
require_once '../../shared/dao/ClienteDAO.php';
require_once '../../shared/dao/ServicoDAO.php';

Auth::requireAuth();

$contratacaoDAO = new ContratacaoDAO();
$clienteDAO = new ClienteDAO();
$servicoDAO = new ServicoDAO();
$user = Auth::getUser();

// Get filter parameters
$filters = [
    'cliente_id' => trim($_GET['cliente_id'] ?? ''),
    'status' => trim($_GET['status'] ?? ''),
    'data_inicio' => trim($_GET['data_inicio'] ?? ''),
    'data_fim' => trim($_GET['data_fim'] ?? ''),
    'servico_nome' => trim($_GET['servico_nome'] ?? '')
];

// Get contracts based on filters
$contratos = $contratacaoDAO->getAll($filters);
$clientes = $clienteDAO->getAll();

// Calculate statistics
$estatisticas = [
    'total' => count($contratos),
    'ativo' => count(array_filter($contratos, fn($c) => $c['status'] === 'ativo')),
    'pendente' => count(array_filter($contratos, fn($c) => $c['status'] === 'pendente')),
    'concluido' => count(array_filter($contratos, fn($c) => $c['status'] === 'concluido')),
    'cancelado' => count(array_filter($contratos, fn($c) => $c['status'] === 'cancelado'))
];

$totalReceita = array_sum(array_column($contratos, 'valor_total'));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Contratos - Sistema de Gestão</title>
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
                            <i class="bi bi-speedometer2"></i> Dashboard
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
                        <a class="nav-link active" href="listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../relatorios/financeiro.php">
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1>Gerenciar Contratos</h1>
                <p class="text-muted">Visualize e gerencie todos os contratos do sistema</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <a href="listar.php" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 1.5rem;"></i>
                            <div class="stats-number"><?= $estatisticas['total'] ?></div>
                            <div class="stats-label">Total</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver todos os contratos</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="listar.php?status=ativo" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                            <div class="stats-number"><?= $estatisticas['ativo'] ?></div>
                            <div class="stats-label">Ativos</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver contratos ativos</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="listar.php?status=pendente" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-clock text-warning" style="font-size: 1.5rem;"></i>
                            <div class="stats-number"><?= $estatisticas['pendente'] ?></div>
                            <div class="stats-label">Pendentes</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver contratos pendentes</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="listar.php?status=concluido" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-check-square text-info" style="font-size: 1.5rem;"></i>
                            <div class="stats-number"><?= $estatisticas['concluido'] ?></div>
                            <div class="stats-label">Concluídos</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver contratos concluídos</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="listar.php?status=cancelado" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-x-circle text-danger" style="font-size: 1.5rem;"></i>
                            <div class="stats-number"><?= $estatisticas['cancelado'] ?></div>
                            <div class="stats-label">Cancelados</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver contratos cancelados</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="../relatorios/financeiro.php" class="text-decoration-none">
                    <div class="card stats-card clickable-card">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar text-success" style="font-size: 1.5rem;"></i>
                            <div class="stats-number">R$ <?= number_format($totalReceita, 0, ',', '.') ?></div>
                            <div class="stats-label">Receita</div>
                            <div class="card-hint">
                                <small><i class="bi bi-arrow-right"></i> Ver relatórios financeiros</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-funnel"></i> Filtros
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-select">
                                <option value="">Todos os clientes</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= $filters['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="ativo" <?= $filters['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="pendente" <?= $filters['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="concluido" <?= $filters['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                <option value="cancelado" <?= $filters['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($filters['data_inicio']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($filters['data_fim']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="servico_nome" class="form-label">Serviço</label>
                            <input type="text" name="servico_nome" id="servico_nome" class="form-control"
                                placeholder="Nome do serviço" value="<?= htmlspecialchars($filters['servico_nome']) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="listar.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Limpar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list"></i> Lista de Contratos
                </h5>
                <span class="badge bg-primary"><?= count($contratos) ?> contratos</span>
            </div>
            <div class="card-body">
                <?php if (empty($contratos)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Nenhum contrato encontrado</h5>
                        <p class="text-muted">Não há contratos que correspondam aos filtros selecionados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Serviços</th>
                                    <th>Valor Total</th>
                                    <th>Data Criação</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contratos as $contrato): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $contrato['id'] ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($contrato['cliente_nome']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($contrato['cliente_email']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $contrato['total_servicos'] ?> serviços</span>
                                        </td>
                                        <td>
                                            <strong>R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?></strong>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($contrato['criado_em'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match ($contrato['status']) {
                                                'ativo' => 'bg-success',
                                                'pendente' => 'bg-warning text-dark',
                                                'concluido' => 'bg-info',
                                                'cancelado' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= ucfirst($contrato['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary"
                                                    onclick="viewContract(<?= $contrato['id'] ?>)"
                                                    title="Ver detalhes">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning"
                                                    onclick="editStatus(<?= $contrato['id'] ?>, '<?= $contrato['status'] ?>')"
                                                    title="Alterar status">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Contract Details Modal -->
    <div class="modal fade" id="contractModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contractDetails">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Edit Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar Status do Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <div class="modal-body">
                        <input type="hidden" id="contract_id" name="contract_id">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">Novo Status</label>
                            <select class="form-select" id="new_status" name="new_status" required>
                                <option value="pendente">Pendente</option>
                                <option value="ativo">Ativo</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (opcional)</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                                placeholder="Motivo da alteração..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alteração</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../shared/assets/js/util.js"></script>

    <script>
        function viewContract(contractId) {
            const modal = new bootstrap.Modal(document.getElementById('contractModal'));

            // Load contract details (admin version)
            fetch(`detalhes_contrato.php?id=${contractId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('contractDetails').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading contract details:', error);
                    document.getElementById('contractDetails').innerHTML =
                        '<div class="alert alert-danger">Erro ao carregar detalhes do contrato.</div>';
                    modal.show();
                });
        }

        function editStatus(contractId, currentStatus) {
            document.getElementById('contract_id').value = contractId;
            document.getElementById('new_status').value = currentStatus;

            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                        location.reload(); // Refresh to show updated status
                    } else {
                        alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                    alert('Erro ao atualizar status.');
                });
        });
    </script>
</body>

</html>