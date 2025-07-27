<?php
require_once '../config/auth.php';

Auth::logout();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
                        <h3 class="mb-3">Logout Realizado</h3>
                        <p class="text-muted mb-4">Você foi desconectado do sistema com sucesso.</p>
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Fazer Login Novamente
                            </a>
                            <a href="../" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Ir para Site Público
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Redirecionar automaticamente após 5 segundos
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000);
    </script>
</body>

</html>