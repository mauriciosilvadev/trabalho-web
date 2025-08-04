<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/UsuarioDAO.php';

Auth::requireAuth();

header('Content-Type: application/json');

try {
    $usuarioDAO = new UsuarioDAO();
    $usuarios = $usuarioDAO->getAll('', 1, 1000); // Buscar todos os usuários
    
    $response = [
        'success' => true,
        'users' => []
    ];
    
    foreach ($usuarios as $usuario) {
        $response['users'][] = [
            'id' => $usuario['id'],
            'ultimo_acesso' => $usuario['ultimo_acesso']
        ];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao buscar dados dos usuários'
    ]);
} 