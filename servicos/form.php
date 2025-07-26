<?php
require_once '../config/auth.php';
require_once '../dao/ServicoDAO.php';

Auth::requireAuth();

$servicoDAO = new ServicoDAO();
$user = Auth::getUser();

$servico = null;
$isEdit = false;
$errors = [];

// Check if editing
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $servico = $servicoDAO->findById($id);
    if ($servico) {
        $isEdit = true;
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

        if (empty($errors)) {
            $data = [
                'nome' => $nome,
                'tipo' => $tipo,
                'preco' => $preco,
                'descricao' => $descricao
            ];

            if ($isEdit) {
                $success = $servicoDAO->update($id, $data);
                $message = $success ? 'Serviço atualizado com sucesso!' : 'Erro ao atualizar serviço.';
            } else {
                $newId = $servicoDAO->create($data);
                $success = $newId !== null;
                $message = $success ? 'Serviço criado com sucesso!' : 'Erro ao criar serviço.';
            }

            if ($success) {
                header('Location: list.php?message=' . urlencode($message));
                exit;
            } else {
                $errors[] = $message;
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
                <h1><?= $isEdit ? 'Editar' : 'Novo' ?> Serviço</h1>
                <p class="text-muted"><?= $isEdit ? 'Edite as informações do serviço' : 'Preencha os dados do novo serviço' ?></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="list.php" class="btn btn-secondary">
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

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="list.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-<?= $isEdit ? 'check' : 'plus' ?>-circle"></i>
                                    <?= $isEdit ? 'Atualizar' : 'Criar' ?> Serviço
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
                                    <p><small class="text-muted">As datas disponíveis são gerenciadas automaticamente no sistema de contratação.</small></p>
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
    <script src="../assets/js/util.js"></script>
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
        });
    </script>
</body>

</html>