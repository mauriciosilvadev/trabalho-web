<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ServicoDAO.php';
require_once '../../shared/dao/DataDisponivelDAO.php';

Auth::requireAuth();

$servicoDAO = new ServicoDAO();
$dataDAO = new DataDisponivelDAO();

$servicos = $servicoDAO->findAll();
$user = Auth::getUser();

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int) $_POST['id'];
    if ($servicoDAO->delete($id)) {
        $message = 'Serviço excluído com sucesso!';
        $messageType = 'success';
        $servicos = $servicoDAO->findAll(); // Refresh list
    } else {
        $message = 'Erro ao excluir serviço. Verifique se não há contratos vinculados.';
        $messageType = 'danger';
    }
}

// Handle success message from form submission
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Sistema de Gestão de Serviços</title>
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
                        <a class="nav-link active" href="list.php">
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
            <div class="col-md-6">
                <h1>Gerenciar Serviços</h1>
                <p class="text-muted">Cadastre, edite e gerencie os serviços disponíveis</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="../dashboard.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Serviço
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Services Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Serviços</h5>
            </div>
            <div class="card-body">
                <?php if (empty($servicos)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3">Nenhum serviço cadastrado</h5>
                        <p class="text-muted">Clique em "Novo Serviço" para começar</p>
                        <a href="form.php" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Primeiro Serviço
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Preço</th>
                                    <th>Datas</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr>
                                        <td><?= $servico['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($servico['nome']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($servico['descricao'], 0, 50)) ?>...</small>
                                        </td>
                                        <td>
                                            <span class="service-type"><?= htmlspecialchars($servico['tipo']) ?></span>
                                        </td>
                                        <td>
                                            <span class="service-price">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $datasDisponiveis = $dataDAO->findAvailableByServiceId($servico['id']);
                                            $disponiveisCount = count($datasDisponiveis);
                                            $usadasCount = 7 - $disponiveisCount;
                                            ?>
                                            <span class="badge bg-success"><?= $disponiveisCount ?></span> disponíveis
                                            <br>
                                            <small class="text-muted"><?= $usadasCount ?>/7 cadastradas</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="form.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="tooltip" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="datas.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="tooltip" title="Gerenciar Datas">
                                                    <i class="bi bi-calendar-check"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteService(<?= $servico['id'] ?>, '<?= htmlspecialchars($servico['nome']) ?>')"
                                                    data-bs-toggle="tooltip" title="Excluir">
                                                    <i class="bi bi-trash"></i>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o serviço <strong id="serviceName"></strong>?</p>
                    <p class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita e só será possível se o serviço não tiver contratos vinculados.
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../shared/assets/js/util.js"></script>
    <script>
        function deleteService(id, name) {
            document.getElementById('serviceName').textContent = name;
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }


    </script>
</body>

</html>
