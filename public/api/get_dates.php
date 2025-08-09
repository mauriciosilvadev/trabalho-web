<?php

/**
 * API Pública - Buscar Datas Disponíveis
 * Não requer autenticação para permitir que clientes vejam datas
 */

require_once '../../shared/dao/DataDisponivelDAO.php';

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

    // Formatar as datas para garantir compatibilidade com JavaScript
    $formattedDates = array_map(function ($date) {
        return [
            'id' => $date['id'],
            'data' => $date['data'],
            'disponivel' => (bool)$date['disponivel'],
            'formatted_date' => date('d/m/Y', strtotime($date['data']))
        ];
    }, $dates);

    echo json_encode($formattedDates);
} catch (Exception $e) {
    error_log("Error fetching dates: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
