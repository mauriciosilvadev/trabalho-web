<?php
require_once '../config/auth.php';
require_once '../dao/DataDisponivelDAO.php';

Auth::requireAuth();

header('Content-Type: application/json');

if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do serviço é obrigatório']);
    exit;
}

$serviceId = (int) $_GET['service_id'];
$dataDAO = new DataDisponivelDAO();

try {
    $dates = $dataDAO->findAvailableByServiceId($serviceId);
    echo json_encode($dates);
} catch (Exception $e) {
    error_log("Error fetching dates: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
