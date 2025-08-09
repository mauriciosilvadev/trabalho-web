<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ServicoDAO.php';
require_once '../../shared/dao/DataDisponivelDAO.php';

Auth::requireAuth();

$servicoDAO = new ServicoDAO();
$dataDisponivelDAO = new DataDisponivelDAO();
$user = Auth::getUser();

$servico = null;
$datasExistentes = [];
$isEdit = false;
$errors = [];

// Detectar origem da navegação
$fromDashboard = isset($_GET['from']) && $_GET['from'] === 'dashboard';

// Check if editing
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $servico = $servicoDAO->findById($id);
    if ($servico) {
        $isEdit = true;
        // Buscar datas existentes para edição
        $datasExistentes = $dataDisponivelDAO->findByServiceId($id);
    } else {
        header('Location: list.php');
        exit;
    }
}

$csrfToken = Auth::generateCSRFToken();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !Auth::verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Token de segurança inválido. Tente novamente.';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $preco = $_POST['preco'] ?? '';
        $descricao = trim($_POST['descricao'] ?? '');

        // Processar datas disponíveis
        $datasDisponiveis = [];
        if (isset($_POST['datas_disponiveis']) && is_array($_POST['datas_disponiveis'])) {
            foreach ($_POST['datas_disponiveis'] as $data) {
                $data = trim($data);
                if (!empty($data)) {
                    // Validar formato da data
                    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
                    if ($dataObj && $dataObj->format('Y-m-d') === $data) {
                        // Verificar se a data não é no passado
                        if ($dataObj >= new DateTime('today')) {
                            $datasDisponiveis[] = $data;
                        }
                    }
                }
            }
            // Remover datas duplicadas
            $datasDisponiveis = array_unique($datasDisponiveis);
            // Ordenar datas
            sort($datasDisponiveis);
        }

        // Validation
        if (empty($nome)) {
            $errors[] = 'Nome é obrigatório.';
        }

        if (empty($tipo)) {
            $errors[] = 'Tipo é obrigatório.';
        }

        if (empty($preco) || !is_numeric($preco) || $preco <= 0) {
            $errors[] = 'Preço deve ser um número positivo.';
        }

        if (empty($descricao)) {
            $errors[] = 'Descrição é obrigatória.';
        }

        // Validar datas disponíveis
        if (count($datasDisponiveis) > 7) {
            $errors[] = 'Máximo de 7 datas disponíveis permitidas.';
        }

        if (empty($errors)) {
            $data = [
                'nome' => $nome,
                'tipo' => $tipo,
                'preco' => $preco,
                'descricao' => $descricao
            ];

            try {
                if ($isEdit) {
                    $success = $servicoDAO->update($id, $data);

                    if ($success) {
                        // Primeiro, remover todas as datas existentes
                        $dataDisponivelDAO->excluirPorServico($id);

                        // Inserir as novas datas (se houver)
                        if (!empty($datasDisponiveis)) {
                            $dataDisponivelDAO->inserirDatas($id, $datasDisponiveis);
                        }
                    }

                    $message = $success ? 'Serviço atualizado com sucesso!' : 'Erro ao atualizar serviço.';
                } else {
                    $newId = $servicoDAO->create($data);
                    $success = $newId !== null;

                    if ($success && !empty($datasDisponiveis)) {
                        // Inserir datas para o novo serviço (apenas se houver datas)
                        $dataDisponivelDAO->inserirDatas($newId, $datasDisponiveis);
                    }

                    $message = $success ? 'Serviço criado com sucesso!' : 'Erro ao criar serviço.';
                }

                if ($success) {
                    // Sempre redirecionar para a listagem de serviços após criar/editar
                    header('Location: list.php?message=' . urlencode($message));
                    exit;
                } else {
                    $errors[] = $message;
                }
            } catch (Exception $e) {
                error_log("Erro ao processar serviço e datas: " . $e->getMessage());
                $errors[] = 'Erro interno do sistema. Tente novamente.';
            }
        }
    }
}

// Get service types for suggestions
$tipos = $servicoDAO->getTypes();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Novo' ?> Serviço - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/assets/css/style.css">
    <link rel="stylesheet" href="../../shared/assets/css/datas-disponiveis.css">
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
            <div class="col-md-8">
                <h1><?= $isEdit ? 'Editar' : 'Novo' ?> Serviço</h1>
                <p class="text-muted"><?= $isEdit ? 'Edite as informações do serviço' : 'Preencha os dados do novo serviço' ?></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= $fromDashboard ? '../dashboard.php' : 'list.php' ?>" class="btn btn-secondary">
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

        <!-- Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus' ?>-circle"></i>
                            Dados do Serviço
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome do Serviço *</label>
                                        <input type="text" class="form-control" id="nome" name="nome"
                                            value="<?= htmlspecialchars($servico['nome'] ?? $_POST['nome'] ?? '') ?>"
                                            required maxlength="100">
                                        <div class="form-text">Nome que será exibido para os clientes</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="preco" class="form-label">Preço (R$) *</label>
                                        <input type="number" class="form-control" id="preco" name="preco"
                                            value="<?= htmlspecialchars($servico['preco'] ?? $_POST['preco'] ?? '') ?>"
                                            required min="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo do Serviço *</label>
                                <input type="text" class="form-control" id="tipo" name="tipo"
                                    value="<?= htmlspecialchars($servico['tipo'] ?? $_POST['tipo'] ?? '') ?>"
                                    required maxlength="50" list="tiposList">
                                <datalist id="tiposList">
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= htmlspecialchars($tipo) ?>">
                                        <?php endforeach; ?>
                                        <option value="Tecnologia">
                                        <option value="Marketing">
                                        <option value="Design">
                                        <option value="Consultoria">
                                        <option value="Manutenção">
                                </datalist>
                                <div class="form-text">Categoria do serviço (ex: Tecnologia, Design, Marketing)</div>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição *</label>
                                <textarea class="form-control" id="descricao" name="descricao"
                                    rows="4" required><?= htmlspecialchars($servico['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea>
                                <div class="form-text">Descreva detalhadamente o que está incluído no serviço</div>
                            </div>

                            <!-- Seção de Datas Disponíveis -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-calendar-date"></i> Datas Disponíveis
                                    <small class="text-muted">(Máximo 7 datas)</small>
                                </label>
                                <div class="card">
                                    <div class="card-body">
                                        <div id="datas-container">
                                            <?php if ($isEdit && !empty($datasExistentes)): ?>
                                                <?php foreach ($datasExistentes as $index => $dataObj): ?>
                                                    <div class="input-group mb-2 data-input-group">
                                                        <input type="date" class="form-control data-input"
                                                            name="datas_disponiveis[]"
                                                            value="<?= htmlspecialchars($dataObj['data']) ?>"
                                                            min="<?= date('Y-m-d') ?>">
                                                        <button type="button" class="btn btn-outline-danger btn-remove-data">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php elseif (!$isEdit): ?>
                                                <!-- Apenas mostrar campo vazio para novos serviços -->
                                                <div class="input-group mb-2 data-input-group">
                                                    <input type="date" class="form-control data-input"
                                                        name="datas_disponiveis[]"
                                                        min="<?= date('Y-m-d') ?>">
                                                    <button type="button" class="btn btn-outline-danger btn-remove-data">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($isEdit && empty($datasExistentes)): ?>
                                                <!-- Para edição sem datas existentes -->
                                                <div class="text-center py-3">
                                                    <p class="text-muted mb-2">
                                                        <i class="bi bi-calendar-x"></i>
                                                        Nenhuma data disponível cadastrada
                                                    </p>
                                                    <button type="button" id="btn-add-first-data" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-plus-circle"></i> Adicionar Primeira Data
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <button type="button" id="btn-add-data" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-plus-circle"></i> Adicionar Data
                                            </button>
                                            <small class="text-muted">
                                                <span id="contador-datas"><?= $isEdit ? count($datasExistentes) : ($isEdit && empty($datasExistentes) ? 0 : 1) ?></span>/7 datas
                                            </small>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            Adicione as datas em que este serviço estará disponível para contratação.
                                            Você pode adicionar até 7 datas.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= $fromDashboard ? '../dashboard.php' : 'list.php' ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-<?= $isEdit ? 'check' : 'plus' ?>-circle"></i>
                                    <?= $isEdit ? 'Atualizar Serviço' : 'Criar Novo Serviço' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <!-- Service Info Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle"></i>
                                Informações do Serviço
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> <?= $servico['id'] ?></p>
                                    <p><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($servico['created_at'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Última atualização:</strong> <?= date('d/m/Y H:i', strtotime($servico['updated_at'])) ?></p>
                                    <p><small class="text-muted">Datas disponíveis: <?= count($datasExistentes) ?> cadastradas</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../shared/assets/js/util.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-format price input
            $('#preco').on('input', function() {
                var value = $(this).val();
                if (value && !isNaN(value) && parseFloat(value) > 0) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            });

            // Character counter for description
            $('#descricao').on('input', function() {
                var current = $(this).val().length;
                var max = 1000;
                var remaining = max - current;

                var counter = $(this).next('.form-text').find('.char-counter');
                if (counter.length === 0) {
                    counter = $('<span class="char-counter"></span>');
                    $(this).next('.form-text').append(' | ').append(counter);
                }

                counter.text(remaining + ' caracteres restantes');

                if (remaining < 50) {
                    counter.addClass('text-warning');
                } else {
                    counter.removeClass('text-warning');
                }
            });

            // Gerenciamento de datas disponíveis
            function atualizarContadorDatas() {
                var total = $('.data-input-group').length;
                $('#contador-datas').text(total);

                // Habilitar/desabilitar botão de adicionar
                if (total >= 7) {
                    $('#btn-add-data').prop('disabled', true);
                } else {
                    $('#btn-add-data').prop('disabled', false);
                }

                // Mostrar/esconder botões de remover
                if (total <= 1) {
                    $('.btn-remove-data').hide();
                } else {
                    $('.btn-remove-data').show();
                }
            }

            // Adicionar nova data
            $('#btn-add-data, #btn-add-first-data').on('click', function() {
                // Se é o primeiro botão, esconder a mensagem vazia
                if ($(this).attr('id') === 'btn-add-first-data') {
                    $(this).closest('.text-center').hide();
                }

                var total = $('.data-input-group').length;
                if (total < 7) {
                    var today = new Date().toISOString().split('T')[0];
                    var novaData = `
                        <div class="input-group mb-2 data-input-group">
                            <input type="date" class="form-control data-input" 
                                   name="datas_disponiveis[]" 
                                   min="${today}">
                            <button type="button" class="btn btn-outline-danger btn-remove-data">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    $('#datas-container').append(novaData);
                    atualizarContadorDatas();
                }
            });

            // Remover data
            $(document).on('click', '.btn-remove-data', function() {
                if ($('.data-input-group').length > 1) {
                    $(this).closest('.data-input-group').remove();
                    atualizarContadorDatas();
                }
            });

            // Validar datas únicas
            $(document).on('change', '.data-input', function() {
                var currentDate = $(this).val();
                var currentInput = $(this);
                var duplicateFound = false;

                $('.data-input').each(function() {
                    if ($(this)[0] !== currentInput[0] && $(this).val() === currentDate && currentDate !== '') {
                        duplicateFound = true;
                        return false;
                    }
                });

                if (duplicateFound) {
                    currentInput.addClass('is-invalid');
                    currentInput.next().after('<div class="invalid-feedback">Esta data já foi selecionada.</div>');
                } else {
                    currentInput.removeClass('is-invalid');
                    currentInput.closest('.input-group').find('.invalid-feedback').remove();
                }
            });

            // Validação antes do envio
            $('form').on('submit', function(e) {
                var datas = [];
                var hasInvalid = false;

                $('.data-input').each(function() {
                    var value = $(this).val();
                    if (value) { // Apenas processar datas não vazias
                        if (datas.includes(value)) {
                            hasInvalid = true;
                            $(this).addClass('is-invalid');
                        } else {
                            datas.push(value);
                            $(this).removeClass('is-invalid');
                        }
                    } else {
                        $(this).removeClass('is-invalid'); // Limpar validação de campos vazios
                    }
                });

                if (hasInvalid) {
                    e.preventDefault();
                    alert('Por favor, remova as datas duplicadas antes de continuar.');
                    return false;
                }

                if (datas.length > 7) {
                    e.preventDefault();
                    alert('Máximo de 7 datas permitidas.');
                    return false;
                }
            });

            // Inicializar contador
            atualizarContadorDatas();

        });
    </script>
</body>

</html>
