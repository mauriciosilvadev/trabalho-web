<?php

/**
 * Página de Detalhes do Contrato (AJAX)
 * Mostra os serviços contratados em um contrato específico
 */

session_start();

// Verificar se cliente está logado
if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Acesso negado</div>';
    exit;
}

require_once '../shared/dao/ContratacaoDAO.php';

$contratoId = $_GET['id'] ?? null;

if (!$contratoId) {
    echo '<div class="alert alert-danger">ID do contrato não informado</div>';
    exit;
}

$contratacaoDAO = new ContratacaoDAO();

// Verificar se o contrato pertence ao cliente logado
$contrato = $contratacaoDAO->buscarPorId($contratoId);
if (!$contrato || $contrato['cliente_id'] != $_SESSION['client_id']) {
    echo '<div class="alert alert-danger">Contrato não encontrado ou acesso negado</div>';
    exit;
}

// Buscar serviços do contrato
$servicos = $contratacaoDAO->listarServicosPorContratacao($contratoId);
?>

<div class="row">
    <div class="col-12">
        <h6 class="mb-3">
            <i class="bi bi-file-text"></i> Contrato #<?= $contratoId ?>
        </h6>

        <div class="row mb-3">
            <div class="col-md-4">
                <small class="text-muted">Status</small>
                <p>
                    <span class="badge bg-<?= $contrato['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($contrato['status']) ?>
                    </span>
                </p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Data</small>
                <p><?= date('d/m/Y H:i', strtotime($contrato['criado_em'])) ?></p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Valor Total</small>
                <p class="fw-bold text-success">R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?></p>
            </div>
        </div>

        <?php if (!empty($contrato['observacoes'])): ?>
            <div class="mb-3">
                <small class="text-muted">Observações</small>
                <p><?= htmlspecialchars($contrato['observacoes']) ?></p>
            </div>
        <?php endif; ?>

        <h6 class="mb-3">Serviços Contratados</h6>

        <?php if (empty($servicos)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Nenhum serviço encontrado para este contrato.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td><?= htmlspecialchars($servico['servico_nome'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($servico['tipo'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <?php if (isset($servico['data'])): ?>
                                        <i class="bi bi-calendar-check text-success"></i>
                                        <?= date('d/m/Y', strtotime($servico['data'])) ?>
                                    <?php else: ?>
                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                        Data não definida
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    R$ <?= number_format($servico['preco'] ?? $servico['valor'] ?? 0, 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
