<?php
require_once '../config/auth.php';

Auth::logout();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Sistema de Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="3;url=../index.php">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body text-center">
                        <h4 class="card-title">Logout realizado com sucesso!</h4>
                        <p class="card-text">Você será redirecionado para a página de login em alguns segundos...</p>
                        <a href="../index.php" class="btn btn-primary">Ir para Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>