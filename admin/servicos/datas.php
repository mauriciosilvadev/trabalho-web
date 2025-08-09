<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ServicoDAO.php';
require_once '../../shared/dao/DataDisponivelDAO.php';

Auth::requireAuth();

$servicoDAO = new ServicoDAO();
$dataDAO = new DataDisponivelDAO();
$user = Auth::getUser();

// Verificar se foi passado um ID de serviço
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$servicoId = (int) $_GET['id'];
$servico = $servicoDAO->findById($servicoId);

if (!$servico) {
    header('Location: list.php');
    exit;
}

// Buscar datas disponíveis do serviço
$datasDisponiveis = $dataDAO->findByServiceId($servicoId);
$datasDisponiveisCount = $dataDAO->contarDatasPorServico($servicoId);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'adicionar_datas') {
        $novasDatas = [];
        if (isset($_POST['novas_datas']) && is_array($_POST['novas_datas'])) {
            foreach ($_POST['novas_datas'] as $data) {
                $data = trim($data);
                if (!empty($data)) {
                    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
                    if ($dataObj && $dataObj->format('Y-m-d') === $data && $dataObj >= new DateTime('today')) {
                        $novasDatas[] = $data;
                    }
                }
            }
        }
        
        $novasDatas = array_unique($novasDatas);
        sort($novasDatas);
        
        // Verificar limite de 7 datas
        $totalDatas = $datasDisponiveisCount + count($novasDatas);
        if ($totalDatas <= 7) {
            try {
                $dataDAO->inserirDatas($servicoId, $novasDatas);
                $message = 'Datas adicionadas com sucesso!';
                $messageType = 'success';
                $datasDisponiveis = $dataDAO->findByServiceId($servicoId);
                $datasDisponiveisCount = $dataDAO->contarDatasPorServico($servicoId);
            } catch (Exception $e) {
                $message = 'Erro ao adicionar datas: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'Limite máximo de 7 datas excedido. Remova algumas datas antes de adicionar novas.';
            $messageType = 'warning';
        }
    } elseif ($action === 'remover_data') {
        $dataId = (int) $_POST['data_id'];
        try {
            $dataDAO->marcarComoIndisponivel($dataId);
            $message = 'Data removida com sucesso!';
            $messageType = 'success';
            $datasDisponiveis = $dataDAO->findByServiceId($servicoId);
            $datasDisponiveisCount = $dataDAO->contarDatasPorServico($servicoId);
        } catch (Exception $e) {
            $message = 'Erro ao remover data: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

$csrfToken = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datas Disponíveis - <?= htmlspecialchars($servico['nome']) ?></title>
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
                <h1>Datas Disponíveis</h1>
                <p class="text-muted">
                    Gerenciando datas para: <strong><?= htmlspecialchars($servico['nome']) ?></strong>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <a href="list.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="form.php?id=<?= $servicoId ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar Serviço
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

        <!-- Service Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informações do Serviço</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Nome:</strong><br>
                                <?= htmlspecialchars($servico['nome']) ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Tipo:</strong><br>
                                <span class="badge bg-primary"><?= htmlspecialchars($servico['tipo']) ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Preço:</strong><br>
                                <span class="text-success">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Datas Cadastradas:</strong><br>
                                <span class="badge bg-info"><?= $datasDisponiveisCount ?>/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add New Dates -->
        <?php if ($datasDisponiveisCount < 7): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-plus-circle"></i> Adicionar Novas Datas
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="addDatesForm">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="action" value="adicionar_datas">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="form-label">Selecione as datas (máximo <?= 7 - $datasDisponiveisCount ?> datas restantes):</label>
                                        <div id="dateInputs">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <input type="date" class="form-control" name="novas_datas[]" 
                                                           min="<?= date('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column h-100 justify-content-end">
                                            <button type="button" class="btn btn-outline-primary mb-2" onclick="addDateInput()">
                                                <i class="bi bi-plus"></i> Adicionar Campo
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check"></i> Salvar Datas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Limite atingido!</strong> Este serviço já possui o máximo de 7 datas cadastradas. 
                Remova algumas datas antes de adicionar novas.
            </div>
        <?php endif; ?>

        <!-- Current Dates -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check"></i> Datas Cadastradas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datasDisponiveis)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                                <h5 class="mt-3">Nenhuma data cadastrada</h5>
                                <p class="text-muted">Adicione datas disponíveis para este serviço</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th>Dias Restantes</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datasDisponiveis as $data): ?>
                                            <?php 
                                            $dataObj = new DateTime($data['data']);
                                            $hoje = new DateTime();
                                            $diasRestantes = $dataObj->diff($hoje)->days;
                                            $statusClass = $data['disponivel'] ? 'success' : 'danger';
                                            $statusText = $data['disponivel'] ? 'Disponível' : 'Indisponível';
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $dataObj->format('d/m/Y') ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= $dataObj->format('l') ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $statusClass ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($diasRestantes == 0): ?>
                                                        <span class="text-warning">Hoje</span>
                                                    <?php elseif ($diasRestantes == 1): ?>
                                                        <span class="text-info">Amanhã</span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= $diasRestantes ?> dias</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($data['disponivel']): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                            <input type="hidden" name="action" value="remover_data">
                                                            <input type="hidden" name="data_id" value="<?= $data['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('Tem certeza que deseja remover esta data?')">
                                                                <i class="bi bi-trash"></i> Remover
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Contratada</span>
                                                    <?php endif; ?>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addDateInput() {
            const container = document.getElementById('dateInputs');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2';
            newRow.innerHTML = `
                <div class="col-md-6">
                    <input type="date" class="form-control" name="novas_datas[]" 
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeDateInput(this)">
                        <i class="bi bi-trash"></i> Remover
                    </button>
                </div>
            `;
            container.appendChild(newRow);
        }

        function removeDateInput(button) {
            button.closest('.row').remove();
        }

        // Validar formulário antes de enviar
        document.getElementById('addDatesForm').addEventListener('submit', function(e) {
            const dateInputs = document.querySelectorAll('input[name="novas_datas[]"]');
            const filledDates = Array.from(dateInputs).filter(input => input.value.trim() !== '');
            
            if (filledDates.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos uma data.');
                return false;
            }
            
            // Verificar datas duplicadas
            const dates = filledDates.map(input => input.value);
            const uniqueDates = [...new Set(dates)];
            
            if (dates.length !== uniqueDates.length) {
                e.preventDefault();
                alert('Por favor, remova as datas duplicadas.');
                return false;
            }
        });
    </script>
</body>

</html> 