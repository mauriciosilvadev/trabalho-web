<?php
require_once '../config/auth.php';
require_once '../dao/UsuarioDAO.php';

Auth::requireAuth();
$user = Auth::getUser();

$usuarioDAO = new UsuarioDAO();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if ($deleteId !== $user['id']) { // Can't delete self
            $success = $usuarioDAO->delete($deleteId);
            $message = $success ? 'Usuário excluído com sucesso!' : 'Erro ao excluir usuário.';
        } else {
            $message = 'Você não pode excluir sua própria conta.';
        }
    }
}

// Get search parameters
$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

// Get users
$usuarios = $usuarioDAO->getAll($search, $page, $perPage);
$totalUsuarios = $usuarioDAO->count($search);
$totalPages = ceil($totalUsuarios / $perPage);

$csrfToken = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Gestão de Serviços</title>
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
                        <a class="nav-link" href="../clientes/list.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contratacao/buscar.php">
                            <i class="bi bi-search"></i> Buscar/Contratar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="list.php">
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
                <h1>Usuários do Sistema</h1>
                <p class="text-muted">Gerencie os usuários que podem acessar o sistema</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="form.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Novo Usuário
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= strpos($message, 'sucesso') !== false ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label for="search" class="form-label">Buscar usuário</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                placeholder="Nome, email ou login...">
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <?php if ($search): ?>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x"></i> Limpar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="row mb-3">
            <div class="col-md-6">
                <p class="text-muted">
                    <?php if ($search): ?>
                        Encontrados <?= $totalUsuarios ?> usuário(s) para "<?= htmlspecialchars($search) ?>"
                    <?php else: ?>
                        Total de <?= $totalUsuarios ?> usuário(s) cadastrado(s)
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Página <?= $page ?> de <?= $totalPages ?>
                    (<?= count($usuarios) ?> registros exibidos)
                </small>
            </div>
        </div>

        <!-- Users Table -->
        <?php if (!empty($usuarios)): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Criado em</th>
                                <th>Último Acesso</th>
                                <th>Status</th>
                                <th width="140">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                                        <?php if ($usuario['id'] == $user['id']): ?>
                                            <span class="badge bg-info ms-1">Você</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($usuario['login']) ?></code>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $usuario['tipo'] === 'admin' ? 'danger' : 'secondary' ?>">
                                            <?= ucfirst($usuario['tipo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($usuario['criado_em'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($usuario['ultimo_acesso']): ?>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Nunca</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="form.php?id=<?= $usuario['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="tooltip" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($usuario['id'] !== $user['id']): ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-user-id="<?= $usuario['id'] ?>"
                                                    data-user-name="<?= htmlspecialchars($usuario['nome']) ?>"
                                                    title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    Próxima <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Nenhum usuário encontrado</h4>
                <p class="text-muted">
                    <?php if ($search): ?>
                        Tente ajustar os termos de busca ou
                        <a href="list.php">ver todos os usuários</a>.
                    <?php else: ?>
                        Comece criando o primeiro usuário do sistema.
                    <?php endif; ?>
                </p>
                <?php if (!$search): ?>
                    <a href="form.php" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Criar Primeiro Usuário
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="delete_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir Usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Handle delete modal
            $('#deleteModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var userId = button.data('user-id');
                var userName = button.data('user-name');

                $('#deleteUserId').val(userId);
                $('#deleteUserName').text(userName);
            });

            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
</body>

</html>