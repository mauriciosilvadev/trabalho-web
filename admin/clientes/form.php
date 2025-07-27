<?php
require_once '../../config/auth.php';
require_once '../../dao/ClienteDAO.php';

Auth::requireAuth();

$clienteDAO = new ClienteDAO();
$user = Auth::getUser();

$cliente = null;
$isEdit = false;
$errors = [];

// Check if editing
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $cliente = $clienteDAO->findById($id);
    if ($cliente) {
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
        $cpf = trim($_POST['cpf'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        // Validation
        if (empty($nome)) {
            $errors[] = 'Nome é obrigatório.';
        }

        if (empty($cpf)) {
            $errors[] = 'CPF é obrigatório.';
        } elseif (!isValidCPF($cpf)) {
            $errors[] = 'CPF inválido.';
        } elseif ($clienteDAO->cpfExists($cpf, $isEdit ? $id : null)) {
            $errors[] = 'CPF já cadastrado para outro cliente.';
        }

        if (empty($cidade)) {
            $errors[] = 'Cidade é obrigatória.';
        }

        if (empty($email)) {
            $errors[] = 'Email é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        } elseif ($clienteDAO->emailExists($email, $isEdit ? $id : null)) {
            $errors[] = 'Email já cadastrado para outro cliente.';
        }

        if (empty($errors)) {
            $data = [
                'nome' => $nome,
                'cpf' => $cpf,
                'cidade' => $cidade,
                'email' => $email,
                'telefone' => !empty($telefone) ? $telefone : null,
                'endereco' => !empty($endereco) ? $endereco : null
            ];

            if ($isEdit) {
                $success = $clienteDAO->update($id, $data);
                $message = $success ? 'Cliente atualizado com sucesso!' : 'Erro ao atualizar cliente.';
            } else {
                $newId = $clienteDAO->create($data);
                $success = $newId !== null;
                $message = $success ? 'Cliente criado com sucesso!' : 'Erro ao criar cliente.';
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

/**
 * Validate CPF
 */
function isValidCPF($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    $digits = str_split($cpf);

    // Validate first digit
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += $digits[$i] * (10 - $i);
    }
    $firstDigit = 11 - ($sum % 11);
    if ($firstDigit >= 10) $firstDigit = 0;

    if ($digits[9] != $firstDigit) return false;

    // Validate second digit
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += $digits[$i] * (11 - $i);
    }
    $secondDigit = 11 - ($sum % 11);
    if ($secondDigit >= 10) $secondDigit = 0;

    return $digits[10] == $secondDigit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Novo' ?> Cliente - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <a class="nav-link active" href="list.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contratacao/listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
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
                            <li><a class="dropdown-item" href="logout.php">
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
                <h1><?= $isEdit ? 'Editar' : 'Novo' ?> Cliente</h1>
                <p class="text-muted"><?= $isEdit ? 'Edite as informações do cliente' : 'Preencha os dados do novo cliente' ?></p>
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
                            <i class="bi bi-<?= $isEdit ? 'pencil' : 'person-plus' ?>"></i>
                            Dados do Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome" name="nome"
                                            value="<?= htmlspecialchars($cliente['nome'] ?? $_POST['nome'] ?? '') ?>"
                                            required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="cpf" class="form-label">CPF *</label>
                                        <input type="text" class="form-control" id="cpf" name="cpf"
                                            value="<?= htmlspecialchars($cliente['cpf'] ?? $_POST['cpf'] ?? '') ?>"
                                            required maxlength="14" placeholder="000.000.000-00">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= htmlspecialchars($cliente['email'] ?? $_POST['email'] ?? '') ?>"
                                            required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefone" class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" id="telefone" name="telefone"
                                            value="<?= htmlspecialchars($cliente['telefone'] ?? $_POST['telefone'] ?? '') ?>"
                                            maxlength="20" placeholder="(00) 00000-0000">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade *</label>
                                <input type="text" class="form-control" id="cidade" name="cidade"
                                    value="<?= htmlspecialchars($cliente['cidade'] ?? $_POST['cidade'] ?? '') ?>"
                                    required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço Completo</label>
                                <textarea class="form-control" id="endereco" name="endereco"
                                    rows="3" maxlength="200"><?= htmlspecialchars($cliente['endereco'] ?? $_POST['endereco'] ?? '') ?></textarea>
                                <div class="form-text">Rua, número, bairro, complemento</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="list.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-<?= $isEdit ? 'check' : 'person-plus' ?>"></i>
                                    <?= $isEdit ? 'Atualizar' : 'Criar' ?> Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script>
        $(document).ready(function() {
            // CPF mask and validation
            $('#cpf').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                $(this).val(value);

                // Validate CPF
                if (value.length === 14) {
                    let cpf = value.replace(/\D/g, '');
                    if (isValidCPF(cpf)) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                    }
                }
            });

            // Phone mask
            $('#telefone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                $(this).val(value);
            });

            // Email validation
            $('#email').on('blur', function() {
                const email = $(this).val();
                if (email && !isValidEmail(email)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>

</html>