<?php
require_once '../config/auth.php';
require_once '../dao/ServicoDAO.php';
require_once '../dao/ClienteDAO.php';
require_once '../dao/ContratacaoDAO.php';

Auth::requireAuth();

// Check if cart and client are selected
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['checkout_client_id'])) {
    header('Location: resumo.php');
    exit;
}

$servicoDAO = new ServicoDAO();
$clienteDAO = new ClienteDAO();
$contratacaoDAO = new ContratacaoDAO();
$user = Auth::getUser();

$cart = $_SESSION['cart'];
$clienteId = $_SESSION['checkout_client_id'];
$cliente = $clienteDAO->findById($clienteId);

if (!$cliente) {
    header('Location: resumo.php');
    exit;
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !Auth::verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Token de segurança inválido. Tente novamente.';
    } else {
        try {
            // Start transaction
            $contratacaoDAO->beginTransaction();

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

            if (empty($cartDetails)) {
                throw new Exception('Carrinho vazio ou serviços inválidos.');
            }

            // Create contract
            $contratoData = [
                'cliente_id' => $clienteId,
                'usuario_id' => $user['id'],
                'valor_total' => $totalGeral,
                'status' => 'ativo',
                'observacoes' => trim($_POST['observacoes'] ?? '')
            ];

            $contratoId = $contratacaoDAO->createContract($contratoData);

            if (!$contratoId) {
                throw new Exception('Erro ao criar contrato.');
            }

            // Add services to contract
            foreach ($cartDetails as $item) {
                $servicoData = [
                    'contrato_id' => $contratoId,
                    'servico_id' => $item['servico']['id'],
                    'quantidade' => $item['quantity'],
                    'preco_unitario' => $item['servico']['preco'],
                    'subtotal' => $item['subtotal']
                ];

                $servicoContratoId = $contratacaoDAO->addService($servicoData);

                if (!$servicoContratoId) {
                    throw new Exception('Erro ao adicionar serviço ao contrato.');
                }

                // Add scheduled dates if any
                if (!empty($item['datas'])) {
                    foreach ($item['datas'] as $data) {
                        $agendamentoData = [
                            'contrato_servico_id' => $servicoContratoId,
                            'data_agendada' => $data,
                            'status' => 'agendado'
                        ];

                        $contratacaoDAO->addScheduledDate($agendamentoData);
                    }
                }
            }

            // Commit transaction
            $contratacaoDAO->commit();

            // Clear cart and client session
            unset($_SESSION['cart']);
            unset($_SESSION['checkout_client_id']);

            $success = true;
            $contratoNumero = str_pad($contratoId, 6, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            $contratacaoDAO->rollback();
            $errors[] = 'Erro ao processar contratação: ' . $e->getMessage();
        }
    }
}

// Get cart details for display
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

$csrfToken = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Contratação - Sistema de Gestão de Serviços</title>
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
        <?php if ($success): ?>
            <!-- Success State -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        <h1 class="mt-3 text-success">Contratação Realizada!</h1>
                        <p class="lead">A contratação foi processada com sucesso.</p>
                    </div>

                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-check-circle"></i> Detalhes da Contratação
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Número do Contrato</h6>
                                    <p class="fs-4 text-primary"><strong>#<?= $contratoNumero ?></strong></p>

                                    <h6>Cliente</h6>
                                    <p>
                                        <strong><?= htmlspecialchars($cliente['nome']) ?></strong><br>
                                        <?= htmlspecialchars($cliente['email']) ?><br>
                                        <?= htmlspecialchars($cliente['cidade']) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Data da Contratação</h6>
                                    <p><?= date('d/m/Y H:i') ?></p>

                                    <h6>Responsável</h6>
                                    <p><?= htmlspecialchars($user['nome']) ?></p>

                                    <h6>Valor Total</h6>
                                    <p class="fs-4 text-success"><strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="../public/dashboard.php" class="btn btn-primary me-2">
                            <i class="bi bi-house"></i> Ir para Dashboard
                        </a>
                        <a href="buscar.php" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Nova Contratação
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Confirmation Form -->

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="buscar.php">Buscar Serviços</a></li>
                    <li class="breadcrumb-item"><a href="resumo.php">Resumo</a></li>
                    <li class="breadcrumb-item active">Confirmar</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h1>Confirmar Contratação</h1>
                    <p class="text-muted">Revise os dados e confirme a contratação</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="resumo.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Contract Details -->
                <div class="col-lg-8">
                    <!-- Client Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person"></i> Cliente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Nome</h6>
                                    <p><?= htmlspecialchars($cliente['nome']) ?></p>

                                    <h6>Email</h6>
                                    <p><?= htmlspecialchars($cliente['email']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>CPF</h6>
                                    <p><?= htmlspecialchars($cliente['cpf']) ?></p>

                                    <h6>Cidade</h6>
                                    <p><?= htmlspecialchars($cliente['cidade']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list-task"></i> Serviços Contratados
                            </h5>
                        </div>
                        <div class="card-body">
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
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-check-circle"></i> Finalizar
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                                <div class="mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes"
                                        rows="3" maxlength="500"
                                        placeholder="Informações adicionais sobre a contratação..."></textarea>
                                    <div class="form-text">Opcional - máximo 500 caracteres</div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Atenção!</strong> Ao confirmar, a contratação será registrada no sistema e não poderá ser desfeita.
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check2-all"></i> Confirmar Contratação
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>