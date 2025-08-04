<?php

/**
 * Página de Detalhes do Contrato para Administrador (AJAX)
 * Mostra os serviços contratados em um contrato específico
 */

require_once '../../shared/config/auth.php';

// Verificar se administrador está logado
Auth::requireAuth();

require_once '../../shared/dao/ContratacaoDAO.php';

$contratoId = $_GET['id'] ?? null;

if (!$contratoId) {
    echo '<div class="alert alert-danger">ID do contrato não informado</div>';
    exit;
}

$contratacaoDAO = new ContratacaoDAO();

// Buscar contrato (administrador pode ver todos)
$contrato = $contratacaoDAO->buscarPorId($contratoId);
if (!$contrato) {
    echo '<div class="alert alert-danger">Contrato não encontrado</div>';
    exit;
}

// Buscar serviços do contrato
$servicos = $contratacaoDAO->listarServicosPorContratacao($contratoId);

// Buscar informações do cliente
require_once '../../shared/dao/ClienteDAO.php';
$clienteDAO = new ClienteDAO();
$cliente = $clienteDAO->buscarPorId($contrato['cliente_id']);
?>

<div class="row">
    <div class="col-12">
        <h6 class="mb-3">
            <i class="bi bi-file-text"></i> Contrato #<?= $contratoId ?>
        </h6>

        <!-- Informações do Cliente -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person"></i> Informações do Cliente</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Nome</small>
                        <p class="mb-2"><strong><?= htmlspecialchars($cliente['nome']) ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Email</small>
                        <p class="mb-2"><?= htmlspecialchars($cliente['email']) ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Telefone</small>
                        <p class="mb-2"><?= htmlspecialchars($cliente['telefone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Cidade</small>
                        <p class="mb-2"><?= htmlspecialchars($cliente['cidade']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Contrato -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Contrato</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">Status</small>
                        <p>
                            <?php
                            $statusClass = match ($contrato['status']) {
                                'ativo' => 'success',
                                'pendente' => 'warning',
                                'concluido' => 'info',
                                'cancelado' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= ucfirst($contrato['status']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Data de Criação</small>
                        <p><?= date('d/m/Y H:i', strtotime($contrato['criado_em'])) ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Valor Total</small>
                        <p class="fw-bold text-success">R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Responsável</small>
                        <p><?= htmlspecialchars($contrato['usuario_nome'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($contrato['observacoes'])): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-chat-text"></i> Observações</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= htmlspecialchars($contrato['observacoes']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Serviços Contratados -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-list-task"></i> Serviços Contratados</h6>
            </div>
            <div class="card-body">
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
                                    <th>Data Agendada</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($servico['servico_nome'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($servico['tipo'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($servico['data']) && $servico['data']): ?>
                                                <i class="bi bi-calendar-check text-success"></i>
                                                <?= date('d/m/Y', strtotime($servico['data'])) ?>
                                            <?php else: ?>
                                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                                <span class="text-warning">Data não definida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ <?= number_format($servico['preco'] ?? 0, 2, ',', '.') ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end">
                                        <strong>R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 