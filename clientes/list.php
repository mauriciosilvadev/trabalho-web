<?php
require_once '../config/auth.php';
require_once '../dao/ClienteDAO.php';

Auth::requireAuth();

$clienteDAO = new ClienteDAO();
$user = Auth::getUser();

$clientes = $clienteDAO->findAll();

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int) $_POST['id'];
    if ($clienteDAO->delete($id)) {
        $message = 'Cliente excluído com sucesso!';
        $messageType = 'success';
        $clientes = $clienteDAO->findAll(); // Refresh list
    } else {
        $message = 'Erro ao excluir cliente. Verifique se não há contratos vinculados.';
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../public/dashboard.php">
                <i class="bi bi-gear-fill"></i> Sistema de Gestão de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../public/dashboard.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../servicos/list.php">
                            <i class="bi bi-list-task"></i> Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="list.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contratacao/buscar.php">
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nome']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../public/logout.php">
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
                <h1>Gerenciar Clientes</h1>
                <p class="text-muted">Cadastre, edite e gerencie os clientes do sistema</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="form.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Novo Cliente
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

        <!-- Clients Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Clientes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($clientes)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3">Nenhum cliente cadastrado</h5>
                        <p class="text-muted">Clique em "Novo Cliente" para começar</p>
                        <a href="form.php" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Cadastrar Primeiro Cliente
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Cidade</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?= $cliente['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($cliente['nome']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($cliente['cpf']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cidade']) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>">
                                                <?= htmlspecialchars($cliente['email']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($cliente['telefone']): ?>
                                                <a href="tel:<?= htmlspecialchars($cliente['telefone']) ?>">
                                                    <?= htmlspecialchars($cliente['telefone']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="form.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="tooltip" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteClient(<?= $cliente['id'] ?>, '<?= htmlspecialchars($cliente['nome']) ?>')"
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
                    <p>Tem certeza que deseja excluir o cliente <strong id="clientName"></strong>?</p>
                    <p class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita e só será possível se o cliente não tiver contratos vinculados.
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
    <script src="../assets/js/util.js"></script>
    <script>
        function deleteClient(id, name) {
            document.getElementById('clientName').textContent = name;
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>

</html>