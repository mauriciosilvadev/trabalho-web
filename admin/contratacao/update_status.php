<?php
require_once '../../config/auth.php';
require_once '../../dao/ContratacaoDAO.php';

Auth::requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $contractId = (int)($_POST['contract_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if (!$contractId || !$newStatus) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }

    $allowedStatuses = ['pendente', 'ativo', 'concluido', 'cancelado'];
    if (!in_array($newStatus, $allowedStatuses)) {
        throw new Exception('Status inválido');
    }

    $contratacaoDAO = new ContratacaoDAO();

    // Check if contract exists
    $contract = $contratacaoDAO->buscarPorId($contractId);
    if (!$contract) {
        throw new Exception('Contrato não encontrado');
    }

    // Update status using the appropriate method
    $success = false;
    switch ($newStatus) {
        case 'confirmada':
        case 'ativo':
            $success = $contratacaoDAO->confirmar($contractId);
            break;
        case 'cancelado':
            $success = $contratacaoDAO->cancelar($contractId);
            break;
        default:
            // For other statuses, we need a general update method
            $success = $contratacaoDAO->updateStatus($contractId, $newStatus);
            break;
    }

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso'
        ]);
    } else {
        throw new Exception('Falha ao atualizar status');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
