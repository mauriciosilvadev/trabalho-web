<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ServicoDAO.php';
require_once '../../shared/dao/ClienteDAO.php';

Auth::requireAuth();

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: buscar.php');
    exit;
}

$servicoDAO = new ServicoDAO();
$clienteDAO = new ClienteDAO();
$user = Auth::getUser();

$cart = $_SESSION['cart'];
$errors = [];
$selectedClient = null;

// Get client if selected
if (isset($_POST['cliente_id']) || isset($_SESSION['checkout_client_id'])) {
    $clienteId = $_POST['cliente_id'] ?? $_SESSION['checkout_client_id'];
    $_SESSION['checkout_client_id'] = $clienteId;
    $selectedClient = $clienteDAO->findById($clienteId);
}

// Get cart details
$cartDetails = [];
$totalGeral = 0;

foreach ($cart as $item) {
    $servico = $servicoDAO->findById($item['servico_id']);
    if ($servico) {
        $subtotal = $servico['preco'] * $item['quantity'];
        $cartDetails[] = [
            'servico' => $servico,
            'quantity' => $item['quantity'],
            'subtotal' => $subtotal,
            'datas' => $item['datas'] ?? []
        ];
        $totalGeral += $subtotal;
    }
}

// Get all clients for selection
$allClients = $clienteDAO->getAll();

$csrfToken = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo da Contratação - Sistema de Gestão de Serviços</title>
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
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="bi bi-cart"></i>
                            <span id="cart-count"><?= count($cart) ?></span> item(s)
                        </span>
                    </li>
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="buscar.php">Buscar Serviços</a></li>
                <li class="breadcrumb-item active">Resumo da Contratação</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Resumo da Contratação</h1>
                <p class="text-muted">Confirme os detalhes antes de finalizar a contratação</p>
            </div>
            <div class="col-md-4 text-end">
            </div>
        </div>

        <div class="row">
            <!-- Cart Summary -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cart-check"></i> Serviços Selecionados
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cartDetails)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                                <h4 class="mt-3">Carrinho vazio</h4>
                                <p class="text-muted">Adicione alguns serviços ao carrinho para continuar.</p>
                                <a href="buscar.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar Serviços
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Serviço</th>
                                            <th>Preço Unit.</th>
                                            <th>Qtd</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartDetails as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['servico']['nome']) ?></strong>
                                                    <?php if (!empty($item['servico']['descricao'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($item['servico']['descricao']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['datas'])): ?>
                                                        <br><small class="text-info">
                                                            <i class="bi bi-calendar"></i>
                                                            Datas: <?= implode(', ', array_map(function ($d) {
                                                                        return date('d/m/Y', strtotime($d));
                                                                    }, $item['datas'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>R$ <?= number_format($item['servico']['preco'], 2, ',', '.') ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td><strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="3">Total Geral</th>
                                            <th>R$ <?= number_format($totalGeral, 2, ',', '.') ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Client Selection -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person"></i> Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($selectedClient): ?>
                            <div class="alert alert-success">
                                <strong><?= htmlspecialchars($selectedClient['nome']) ?></strong><br>
                                <small>
                                    <?= htmlspecialchars($selectedClient['email']) ?><br>
                                    <?= htmlspecialchars($selectedClient['cidade']) ?>
                                    <?php if ($selectedClient['telefone']): ?>
                                        <br><?= htmlspecialchars($selectedClient['telefone']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="$('#clientModal').modal('show')">
                                <i class="bi bi-pencil"></i> Alterar Cliente
                            </button>
                        <?php else: ?>
                            <p class="text-muted">Selecione o cliente para esta contratação.</p>
                            <button type="button" class="btn btn-primary" onclick="$('#clientModal').modal('show')">
                                <i class="bi bi-person-plus"></i> Selecionar Cliente
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($selectedClient && !empty($cartDetails)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-check-circle"></i> Finalizar
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid">
                                <a href="confirmar.php" class="btn btn-success btn-lg">
                                    <i class="bi bi-check2-all"></i> Confirmar Contratação
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2 text-center">
                                Ao confirmar, a contratação será registrada no sistema
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Client Selection Modal -->
    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person"></i> Selecionar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="clientSearch" placeholder="Buscar cliente por nome, email ou CPF...">
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="row" id="clientList">
                            <?php foreach ($allClients as $cliente): ?>
                                <div class="col-md-6 mb-3 client-item"
                                    data-search="<?= htmlspecialchars(strtolower($cliente['nome'] . ' ' . $cliente['email'] . ' ' . $cliente['cpf'])) ?>">
                                    <div class="card <?= $selectedClient && $selectedClient['id'] == $cliente['id'] ? 'border-success' : '' ?>">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cliente_id"
                                                    value="<?= $cliente['id'] ?>" id="cliente_<?= $cliente['id'] ?>"
                                                    <?= $selectedClient && $selectedClient['id'] == $cliente['id'] ? 'checked' : '' ?>>
                                                <label class="form-check-label w-100" for="cliente_<?= $cliente['id'] ?>">
                                                    <strong><?= htmlspecialchars($cliente['nome']) ?></strong><br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($cliente['email']) ?><br>
                                                        CPF: <?= htmlspecialchars($cliente['cpf']) ?><br>
                                                        <?= htmlspecialchars($cliente['cidade']) ?>
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (empty($allClients)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Nenhum cliente encontrado</h5>
                                <p class="text-muted">Cadastre clientes antes de fazer contratações.</p>
                                <a href="../clientes/form.php" class="btn btn-primary" target="_blank">
                                    <i class="bi bi-person-plus"></i> Cadastrar Cliente
                                </a>
                            </div>
                        <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" <?= empty($allClients) ? 'disabled' : '' ?>>
                        <i class="bi bi-check"></i> Selecionar Cliente
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
            // Client search functionality
            $('#clientSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();

                $('.client-item').each(function() {
                    const clientData = $(this).data('search');
                    if (clientData.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Highlight selected client
            $('input[name="cliente_id"]').on('change', function() {
                $('.client-item .card').removeClass('border-success');
                $(this).closest('.card').addClass('border-success');
            });
        });
    </script>
</body>

</html>
