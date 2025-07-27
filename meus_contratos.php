<?php

/**
 * Página do Cliente - Meus Contratos
 * Permite que clientes vejam suas contratações
 */

session_start();

require_once 'dao/ContratacaoDAO.php';
require_once 'dao/ClienteDAO.php';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: login.php?redirect=meus_contratos.php');
    exit;
}

$contratacaoDAO = new ContratacaoDAO();
$clienteDAO = new ClienteDAO();

$clienteId = $_SESSION['client_id'];
$cliente = $clienteDAO->findById($clienteId);

if (!$cliente) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get client contracts
$contratos = $contratacaoDAO->buscarPorCliente($clienteId);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Contratos - Sistema de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="buscar.php">
                <i class="bi bi-building"></i> Sistema de Serviços
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="buscar.php">
                            <i class="bi bi-search"></i> Buscar Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="meus_contratos.php">
                            <i class="bi bi-file-text"></i> Meus Contratos
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-file-text"></i> Meus Contratos</h2>
                <p class="text-muted">Visualize suas contratações de serviços</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="buscar.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nova Contratação
                </a>
            </div>
        </div>

        <!-- Client Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5><?= htmlspecialchars($cliente['nome']) ?></h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($cliente['email']) ?> &nbsp;&nbsp;
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($cliente['cidade']) ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-success fs-6"><?= count($contratos) ?> contrato(s)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contracts -->
        <?php if (empty($contratos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-x text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Nenhum contrato encontrado</h4>
                <p class="text-muted">Você ainda não fez nenhuma contratação de serviços.</p>
                <a href="buscar.php" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar Serviços
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($contratos as $contrato): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-file-text"></i>
                                    Contrato #<?= str_pad($contrato['id'], 6, '0', STR_PAD_LEFT) ?>
                                </h6>
                                <span class="badge bg-<?= $contrato['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($contrato['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Data da Contratação</small>
                                        <p class="mb-2"><?= date('d/m/Y', strtotime($contrato['criado_em'])) ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Valor Total</small>
                                        <p class="mb-2 fw-bold text-success">
                                            R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($contrato['observacoes'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Observações</small>
                                        <p class="small"><?= htmlspecialchars($contrato['observacoes']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="verDetalhes(<?= $contrato['id'] ?>)">
                                    <i class="bi bi-eye"></i> Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Contract Details Modal -->
    <div class="modal fade" id="contractModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text"></i> Detalhes do Contrato
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contractDetails">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalhes(contratoId) {
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('contractModal'));
            modal.show();

            // Load contract details
            $.ajax({
                url: 'detalhes_contrato.php',
                type: 'GET',
                data: {
                    id: contratoId
                },
                success: function(response) {
                    $('#contractDetails').html(response);
                },
                error: function() {
                    $('#contractDetails').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Erro ao carregar detalhes do contrato.
                        </div>
                    `);
                }
            });
        }
    </script>
</body>

</html>