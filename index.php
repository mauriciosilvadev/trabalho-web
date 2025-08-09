<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-body text-center p-5">
                        <h1 class="mb-4">🏢 Sistema de Serviços</h1>
                        <p class="lead mb-4">Escolha a área que deseja acessar:</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">👥 Área Pública</h5>
                                        <p class="card-text">Busque e contrate serviços</p>
                                        <a href="public/" class="btn btn-primary">Acessar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">⚙️ Área Administrativa</h5>
                                        <p class="card-text">Gerenciar sistema</p>
                                        <a href="admin/" class="btn btn-warning">Acessar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect após 3 segundos se preferir
        // setTimeout(() => window.location.href = 'public/', 3000);
    </script>
</body>

</html>