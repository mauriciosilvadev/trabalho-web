<?php

/**
 * Página Pública - Cadastro de Cliente
 * Permite que clientes se cadastrem para contratar serviços
 */

session_start();

require_once 'dao/ClienteDAO.php';

$clienteDAO = new ClienteDAO();
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // Validation
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório.';
    }

    if (empty($cpf)) {
        $errors[] = 'CPF é obrigatório.';
    } elseif (!isValidCPF($cpf)) {
        $errors[] = 'CPF inválido.';
    } elseif ($clienteDAO->cpfExists($cpf)) {
        $errors[] = 'CPF já cadastrado.';
    }

    if (empty($cidade)) {
        $errors[] = 'Cidade é obrigatória.';
    }

    if (empty($email)) {
        $errors[] = 'Email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    } elseif ($clienteDAO->emailExists($email)) {
        $errors[] = 'Email já cadastrado.';
    }

    if (!empty($senha)) {
        if (strlen($senha) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres.';
        } elseif ($senha !== $confirmarSenha) {
            $errors[] = 'Confirmação de senha não confere.';
        }
    }

    if (empty($errors)) {
        $data = [
            'nome' => $nome,
            'cpf' => $cpf,
            'cidade' => $cidade,
            'email' => $email,
            'telefone' => $telefone,
            'endereco' => $endereco,
            'senha' => !empty($senha) ? password_hash($senha, PASSWORD_DEFAULT) : null
        ];

        $newId = $clienteDAO->create($data);
        if ($newId) {
            $success = true;

            // Auto-login if password was provided
            if (!empty($senha)) {
                $_SESSION['client_id'] = $newId;
                $_SESSION['client_name'] = $nome;
                $_SESSION['client_email'] = $email;
            }
        } else {
            $errors[] = 'Erro ao cadastrar cliente.';
        }
    }
}

/**
 * Validate CPF
 */
function isValidCPF($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11 || preg_match('/^(\d)\1*$/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre-se - Sistema de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="buscar.php">
                <i class="bi bi-building"></i> Sistema de Serviços
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="buscar.php">
                    <i class="bi bi-search"></i> Buscar Serviços
                </a>
                <a class="nav-link" href="login.php">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a class="nav-link" href="admin/">
                    <i class="bi bi-gear"></i> Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">
                                <i class="bi bi-check-circle"></i> Cadastro Realizado!
                            </h4>
                        </div>
                        <div class="card-body text-center">
                            <p class="lead">Seu cadastro foi realizado com sucesso!</p>
                            <p>Agora você pode contratar nossos serviços.</p>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="buscar.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar Serviços
                                </a>
                                <?php if (!empty($_POST['senha'])): ?>
                                    <a href="login.php" class="btn btn-outline-success">
                                        <i class="bi bi-box-arrow-in-right"></i> Fazer Login
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="bi bi-person-plus"></i> Cadastro de Cliente
                            </h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Preencha seus dados para contratar nossos serviços</p>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" novalidate>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="nome" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="nome" name="nome"
                                                value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cpf" class="form-label">CPF *</label>
                                            <input type="text" class="form-control" id="cpf" name="cpf"
                                                value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>"
                                                placeholder="000.000.000-00" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="telefone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="telefone" name="telefone"
                                                value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                                                placeholder="(27) 99999-9999">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="cidade" class="form-label">Cidade *</label>
                                            <input type="text" class="form-control" id="cidade" name="cidade"
                                                value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="endereco" class="form-label">Endereço</label>
                                    <input type="text" class="form-control" id="endereco" name="endereco"
                                        value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>"
                                        placeholder="Rua, número, bairro (opcional)">
                                </div>

                                <hr>

                                <h6 class="text-muted">Criar senha (opcional)</h6>
                                <p class="text-muted small">Crie uma senha para acessar rapidamente suas contratações futuras</p>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="senha" class="form-label">Senha</label>
                                            <input type="password" class="form-control" id="senha" name="senha"
                                                placeholder="Mínimo 6 caracteres">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="buscar.php" class="btn btn-outline-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-person-plus"></i> Cadastrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script>
        // Format CPF input
        $('#cpf').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(value);
        });

        // Format phone input
        $('#telefone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            $(this).val(value);
        });

        // Password confirmation validation
        $('#confirmar_senha').on('input', function() {
            const senha = $('#senha').val();
            const confirmar = $(this).val();

            if (senha && confirmar && senha !== confirmar) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    </script>
</body>

</html>