<?php

/**
 * Página pública - Buscar Serviços
 * Esta página NÃO requer autenticação
 */

session_start(); // Necessário para verificar se cliente está logado

require_once '../shared/dao/ServicoDAO.php';

try {
    $servicoDAO = new ServicoDAO();

    // Filtros de busca adaptados para os métodos existentes
    $filtros = [
        'nome' => $_GET['nome'] ?? '',
        'tipo' => $_GET['tipo'] ?? '',
        'preco_min' => $_GET['preco_min'] ?? '',
        'preco_max' => $_GET['preco_max'] ?? ''
    ];

    // Usar o método search existente
    if (array_filter($filtros)) {
        $servicos = $servicoDAO->search($filtros);
    } else {
        // Buscar todos os serviços com datas disponíveis
        $servicos = $servicoDAO->findWithAvailableDates();
    }

    // Buscar tipos únicos para filtro usando método existente
    $tipos = $servicoDAO->getTypes();
} catch (Exception $e) {
    $erro = "Erro ao buscar serviços: " . $e->getMessage();
    $servicos = [];
    $tipos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Serviços - Sistema de Contratação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../shared/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Sistema de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="buscar.php">
                            <i class="bi bi-search"></i> Buscar Serviços
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <!-- Carrinho -->
                    <li class="nav-item">
                        <a class="nav-link cart-button" href="carrinho.php" style="display: none;">
                            <i class="bi bi-cart"></i> Carrinho
                            <span class="badge bg-light text-dark cart-badge">0</span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['client_id'])): ?>
                        <!-- Cliente logado -->
                        <li class="nav-item">
                            <a class="nav-link" href="meus_contratos.php">
                                <i class="bi bi-file-text"></i> Meus Contratos
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['client_name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout_cliente.php">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Cliente não logado -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">
                                <i class="bi bi-person-plus"></i> Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Link Admin -->
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="bi bi-search"></i> Buscar Serviços</h2>
                <p class="text-muted">Encontre e contrate os serviços que você precisa</p>
            </div>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Serviço</label>
                            <select name="tipo" class="form-select">
                                <option value="">Todos os tipos</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= htmlspecialchars($tipo) ?>" <?= $filtros['tipo'] === $tipo ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Nome do Serviço</label>
                            <input type="text" name="nome" class="form-control"
                                value="<?= htmlspecialchars($filtros['nome']) ?>"
                                placeholder="Digite o nome do serviço">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Preço Mínimo</label>
                            <input type="number" name="preco_min" class="form-control"
                                value="<?= htmlspecialchars($filtros['preco_min']) ?>"
                                placeholder="R$ 0,00" step="0.01" min="0">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Preço Máximo</label>
                            <input type="number" name="preco_max" class="form-control"
                                value="<?= htmlspecialchars($filtros['preco_max']) ?>"
                                placeholder="R$ 999,99" step="0.01" min="0">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (array_filter($filtros)): ?>
                        <div class="mt-3">
                            <a href="buscar.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Limpar Filtros
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="row">
            <?php if (empty($servicos)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Nenhum serviço encontrado</h4>
                        <p class="text-muted">Tente ajustar os filtros de busca</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($servicos as $servico): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($servico['nome']) ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-secondary mb-2"><?= htmlspecialchars($servico['tipo']) ?></span><br>
                                    <?php if (isset($servico['datas_disponiveis'])): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-check"></i> <?= $servico['datas_disponiveis'] ?? 0 ?> data(s) disponível(is)
                                        </small>
                                    <?php endif; ?>
                                </p>

                                <?php if ($servico['descricao']): ?>
                                    <p class="card-text">
                                        <?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>
                                        <?= strlen($servico['descricao']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-primary mb-0">
                                        R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                    </h5>
                                    <button type="button" class="btn btn-primary add-to-cart"
                                        data-service-id="<?= $servico['id'] ?>"
                                        data-service-name="<?= htmlspecialchars($servico['nome']) ?>"
                                        data-service-type="<?= htmlspecialchars($servico['tipo']) ?>"
                                        data-service-price="<?= $servico['preco'] ?>">
                                        <i class="bi bi-cart-plus"></i> Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast para mensagens -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Sistema</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../admin/contratacao/carrinho.js"></script>
    <script>
        // Função para mostrar mensagens
        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showError(message) {
            showToast(message, 'error');
        }

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastBody = toast.querySelector('.toast-body');

            toastBody.textContent = message;
            toast.className = 'toast ' + (type === 'success' ? 'bg-success text-white' : 'bg-danger text-white');

            new bootstrap.Toast(toast).show();
        }

        // Inicializar cart display ao carregar página
        $(document).ready(function() {
            updateCartDisplay();
        });
    </script>
</body>

</html>