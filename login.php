<?php

/**
 * Página Pública - Login de Cliente
 * Permite que clientes façam login para acessar seus contratos
 */

session_start();

require_once 'dao/ClienteDAO.php';

$clienteDAO = new ClienteDAO();
$error = '';

// Check if client is already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: meus_contratos.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $cliente = $clienteDAO->findByEmail($email);

        if ($cliente && !empty($cliente['senha']) && password_verify($senha, $cliente['senha'])) {
            // Login successful
            $_SESSION['client_id'] = $cliente['id'];
            $_SESSION['client_name'] = $cliente['nome'];
            $_SESSION['client_email'] = $cliente['email'];

            // Redirect to intended page or contracts
            $redirect = $_GET['redirect'] ?? 'meus_contratos.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - Sistema de Serviços</title>
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
                <a class="nav-link" href="cadastro.php">
                    <i class="bi bi-person-plus"></i> Cadastrar
                </a>
                <a class="nav-link" href="admin/">
                    <i class="bi bi-gear"></i> Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-person-circle"></i> Login Cliente
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                                </button>
                            </div>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="text-muted small">Não tem conta?</p>
                            <a href="cadastro.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Cadastrar-se
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <p class="text-muted small">Ou continue sem login:</p>
                            <a href="buscar.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-search"></i> Buscar Serviços
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>