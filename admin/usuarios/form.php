<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/UsuarioDAO.php';

Auth::requireAuth();

$usuarioDAO = new UsuarioDAO();
$user = Auth::getUser();

$usuario = null;
$isEdit = false;
$errors = [];

// Check if editing
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $usuario = $usuarioDAO->findById($id);
    if ($usuario) {
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
        $login = trim($_POST['login'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $tipo = $_POST['tipo'] ?? 'operador';
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        // Validation
        if (empty($nome)) {
            $errors[] = 'Nome é obrigatório.';
        }

        if (empty($login)) {
            $errors[] = 'Login é obrigatório.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login)) {
            $errors[] = 'Login deve ter entre 3 e 20 caracteres, apenas letras, números e underscore.';
        } elseif ($usuarioDAO->loginExists($login, $isEdit ? $id : null)) {
            $errors[] = 'Login já está em uso.';
        }

        if (empty($email)) {
            $errors[] = 'Email é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        } elseif ($usuarioDAO->emailExists($email, $isEdit ? $id : null)) {
            $errors[] = 'Email já está em uso.';
        }

        // Password validation
        if (!$isEdit) {
            // Creating new user - password required
            if (empty($senha)) {
                $errors[] = 'Senha é obrigatória.';
            } elseif (strlen($senha) < 6) {
                $errors[] = 'Senha deve ter pelo menos 6 caracteres.';
            } elseif ($senha !== $confirmarSenha) {
                $errors[] = 'Confirmação de senha não confere.';
            }
        } else {
            // Editing user - password optional
            if (!empty($senha)) {
                if (strlen($senha) < 6) {
                    $errors[] = 'Senha deve ter pelo menos 6 caracteres.';
                } elseif ($senha !== $confirmarSenha) {
                    $errors[] = 'Confirmação de senha não confere.';
                }
            }
        }

        if (!in_array($tipo, ['admin', 'operador'])) {
            $errors[] = 'Tipo de usuário inválido.';
        }

        if (empty($errors)) {
            $data = [
                'nome' => $nome,
                'login' => $login,
                'email' => $email,
                'tipo' => $tipo,
                'ativo' => $ativo
            ];

            // Only update password if provided
            if (!empty($senha)) {
                $data['senha'] = $senha;
            }

            if ($isEdit) {
                $success = $usuarioDAO->update($id, $data);
                $message = $success ? 'Usuário atualizado com sucesso!' : 'Erro ao atualizar usuário.';
            } else {
                $newId = $usuarioDAO->create($data);
                $success = $newId !== null;
                $message = $success ? 'Usuário criado com sucesso!' : 'Erro ao criar usuário.';
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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Novo' ?> Usuário - Sistema de Gestão de Serviços</title>
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
                        <a class="nav-link" href="../contratacao/listar.php">
                            <i class="bi bi-file-earmark-text"></i> Contratos
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
                <h1><?= $isEdit ? 'Editar' : 'Novo' ?> Usuário</h1>
                <p class="text-muted"><?= $isEdit ? 'Edite as informações do usuário' : 'Preencha os dados do novo usuário' ?></p>
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
                            Dados do Usuário
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
                                            value="<?= htmlspecialchars($usuario['nome'] ?? $_POST['nome'] ?? '') ?>"
                                            required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="login" class="form-label">Login *</label>
                                        <input type="text" class="form-control" id="login" name="login"
                                            value="<?= htmlspecialchars($usuario['login'] ?? $_POST['login'] ?? '') ?>"
                                            required maxlength="20" pattern="[a-zA-Z0-9_]{3,20}">
                                        <div class="form-text">3-20 caracteres: letras, números e _</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= htmlspecialchars($usuario['email'] ?? $_POST['email'] ?? '') ?>"
                                            required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label">Tipo de Usuário *</label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="operador" <?= ($usuario['tipo'] ?? $_POST['tipo'] ?? '') === 'operador' ? 'selected' : '' ?>>
                                                Operador
                                            </option>
                                            <option value="admin" <?= ($usuario['tipo'] ?? $_POST['tipo'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                                Administrador
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="senha" class="form-label">
                                            Senha <?= $isEdit ? '(deixe em branco para manter atual)' : '*' ?>
                                        </label>
                                        <input type="password" class="form-control" id="senha" name="senha"
                                            minlength="6" <?= !$isEdit ? 'required' : '' ?>>
                                        <div class="form-text">Mínimo 6 caracteres</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirmar_senha" class="form-label">
                                            Confirmar Senha <?= $isEdit ? '' : '*' ?>
                                        </label>
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                            minlength="6" <?= !$isEdit ? 'required' : '' ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo"
                                        <?= ($usuario['ativo'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ativo">
                                        Usuário ativo (pode fazer login no sistema)
                                    </label>
                                </div>
                            </div>

                            <?php if ($isEdit && $usuario['id'] == $user['id']): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Você está editando sua própria conta. Algumas alterações podem afetar sua sessão atual.
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="list.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-<?= $isEdit ? 'check' : 'person-plus' ?>"></i>
                                    <?= $isEdit ? 'Atualizar' : 'Criar' ?> Usuário
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
            // Password confirmation validation
            $('#confirmar_senha').on('input', function() {
                const senha = $('#senha').val();
                const confirmarSenha = $(this).val();

                if (confirmarSenha && senha !== confirmarSenha) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Login validation
            $('#login').on('input', function() {
                const login = $(this).val();
                const pattern = /^[a-zA-Z0-9_]{3,20}$/;

                if (login && !pattern.test(login)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
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

            // Password strength indicator
            $('#senha').on('input', function() {
                const senha = $(this).val();
                if (senha.length >= 6) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else if (senha.length > 0) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });
        });
    </script>
</body>

</html>
