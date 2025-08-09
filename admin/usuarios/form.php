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

        // Validation - Nome Completo (apenas letras e espaços)
        if (empty($nome)) {
            $errors[] = 'Nome completo é obrigatório.';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s]{2,100}$/', $nome)) {
            $errors[] = 'Nome deve conter apenas letras e espaços, entre 2 e 100 caracteres.';
        }

        // Validation - Login
        if (empty($login)) {
            $errors[] = 'Login é obrigatório.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login)) {
            $errors[] = 'Login deve ter entre 3 e 20 caracteres, apenas letras, números e underscore (_).';
        } elseif ($usuarioDAO->loginExists($login, $isEdit ? $id : null)) {
            $errors[] = 'Login já está em uso.';
        }

        // Validation - Email
        if (empty($email)) {
            $errors[] = 'Email é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Formato de email inválido. Use um email válido (ex: usuario@dominio.com).';
        } elseif ($usuarioDAO->emailExists($email, $isEdit ? $id : null)) {
            $errors[] = 'Email já está em uso.';
        }

        // Password validation - Mais rigorosa
        if (!$isEdit) {
            // Creating new user - password required
            if (empty($senha)) {
                $errors[] = 'Senha é obrigatória.';
            } elseif (strlen($senha) < 8) {
                $errors[] = 'Senha deve ter pelo menos 8 caracteres.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $senha)) {
                $errors[] = 'Senha deve conter: mínimo 8 caracteres, 1 letra maiúscula, 1 letra minúscula, 1 número e 1 caractere especial (@$!%*?&).';
            } elseif ($senha !== $confirmarSenha) {
                $errors[] = 'Confirmação de senha não confere.';
            }
        } else {
            // Editing user - password optional
            if (!empty($senha)) {
                if (strlen($senha) < 8) {
                    $errors[] = 'Senha deve ter pelo menos 8 caracteres.';
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $senha)) {
                    $errors[] = 'Senha deve conter: mínimo 8 caracteres, 1 letra maiúscula, 1 letra minúscula, 1 número e 1 caractere especial (@$!%*?&).';
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
                'ativo' => 1 // Sempre ativo por padrão
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
    <style>
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .password-requirements {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        .requirement i {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }
        .requirement.met {
            color: #198754;
        }
        .requirement.not-met {
            color: #dc3545;
        }
    </style>
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
                        <a class="nav-link" href="../relatorios/financeiro.php">
                            Relatórios Financeiros
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
                        <form method="POST" action="" id="userForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome" name="nome"
                                            value="<?= htmlspecialchars($usuario['nome'] ?? $_POST['nome'] ?? '') ?>"
                                            required maxlength="100" pattern="[a-zA-ZÀ-ÿ\s]{2,100}">
                                        <div class="form-text">Apenas letras e espaços, entre 2 e 100 caracteres</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="login" class="form-label">Login *</label>
                                        <input type="text" class="form-control" id="login" name="login"
                                            value="<?= htmlspecialchars($usuario['login'] ?? $_POST['login'] ?? '') ?>"
                                            required maxlength="20" pattern="[a-zA-Z0-9_]{3,20}">
                                        <div class="form-text">3-20 caracteres: letras, números e underscore (_)</div>
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
                                        <div class="form-text">Formato válido: usuario@dominio.com</div>
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
                                            minlength="8" <?= !$isEdit ? 'required' : '' ?>>
                                        <div class="password-requirements">
                                            <div class="requirement" id="req-length">
                                                <i class="bi bi-circle"></i> Mínimo 8 caracteres
                                            </div>
                                            <div class="requirement" id="req-uppercase">
                                                <i class="bi bi-circle"></i> 1 letra maiúscula
                                            </div>
                                            <div class="requirement" id="req-lowercase">
                                                <i class="bi bi-circle"></i> 1 letra minúscula
                                            </div>
                                            <div class="requirement" id="req-number">
                                                <i class="bi bi-circle"></i> 1 número
                                            </div>
                                            <div class="requirement" id="req-special">
                                                <i class="bi bi-circle"></i> 1 caractere especial (@$!%*?&)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirmar_senha" class="form-label">
                                            Confirmar Senha <?= $isEdit ? '' : '*' ?>
                                        </label>
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                            minlength="8" <?= !$isEdit ? 'required' : '' ?>>
                                        <div class="form-text" id="password-match">As senhas devem ser iguais</div>
                                    </div>
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
    <script>
        $(document).ready(function() {
            // Nome validation - apenas letras e espaços
            $('#nome').on('input', function() {
                const nome = $(this).val();
                const pattern = /^[a-zA-ZÀ-ÿ\s]{2,100}$/;
                
                if (nome && !pattern.test(nome)) {
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
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailPattern.test(email)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Password strength validation
            $('#senha').on('input', function() {
                const senha = $(this).val();
                
                // Verificar cada requisito
                const hasLength = senha.length >= 8;
                const hasUppercase = /[A-Z]/.test(senha);
                const hasLowercase = /[a-z]/.test(senha);
                const hasNumber = /\d/.test(senha);
                const hasSpecial = /[@$!%*?&]/.test(senha);
                
                // Atualizar indicadores visuais
                updateRequirement('req-length', hasLength);
                updateRequirement('req-uppercase', hasUppercase);
                updateRequirement('req-lowercase', hasLowercase);
                updateRequirement('req-number', hasNumber);
                updateRequirement('req-special', hasSpecial);
                
                // Validar confirmação de senha
                validatePasswordMatch();
            });

            // Password confirmation validation
            $('#confirmar_senha').on('input', function() {
                validatePasswordMatch();
            });

            function updateRequirement(elementId, isMet) {
                const element = $('#' + elementId);
                const icon = element.find('i');
                
                if (isMet) {
                    element.removeClass('not-met').addClass('met');
                    icon.removeClass('bi-circle').addClass('bi-check-circle-fill');
                } else {
                    element.removeClass('met').addClass('not-met');
                    icon.removeClass('bi-check-circle-fill').addClass('bi-circle');
                }
            }

            function validatePasswordMatch() {
                const senha = $('#senha').val();
                const confirmarSenha = $('#confirmar_senha').val();
                const matchElement = $('#password-match');
                
                if (confirmarSenha && senha !== confirmarSenha) {
                    $('#confirmar_senha').addClass('is-invalid');
                    matchElement.text('As senhas não coincidem').removeClass('text-success').addClass('text-danger');
                } else if (confirmarSenha && senha === confirmarSenha) {
                    $('#confirmar_senha').removeClass('is-invalid').addClass('is-valid');
                    matchElement.text('Senhas coincidem').removeClass('text-danger').addClass('text-success');
                } else {
                    $('#confirmar_senha').removeClass('is-invalid is-valid');
                    matchElement.text('As senhas devem ser iguais').removeClass('text-success text-danger');
                }
            }

            // Form validation before submit
            $('#userForm').on('submit', function(e) {
                let isValid = true;
                
                // Validar nome
                const nome = $('#nome').val();
                if (!nome || !/^[a-zA-ZÀ-ÿ\s]{2,100}$/.test(nome)) {
                    $('#nome').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validar login
                const login = $('#login').val();
                if (!login || !/^[a-zA-Z0-9_]{3,20}$/.test(login)) {
                    $('#login').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validar email
                const email = $('#email').val();
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#email').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validar senha se for criação ou se foi preenchida
                const senha = $('#senha').val();
                const confirmarSenha = $('#confirmar_senha').val();
                const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
                
                if (!isEdit || senha) {
                    if (!senha || senha.length < 8 || !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(senha)) {
                        $('#senha').addClass('is-invalid');
                        isValid = false;
                    }
                    
                    if (!confirmarSenha || senha !== confirmarSenha) {
                        $('#confirmar_senha').addClass('is-invalid');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, corrija os erros no formulário antes de continuar.');
                }
            });
        });
    </script>
</body>

</html>