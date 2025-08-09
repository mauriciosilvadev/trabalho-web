<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testando public/index.php...<br>";

try {
    require_once '../shared/config/db.php';
    echo "✅ Config carregado<br>";

    require_once '../shared/dao/ServicoDAO.php';
    echo "✅ DAO carregado<br>";

    $servicoDAO = new ServicoDAO();
    echo "✅ ServicoDAO instanciado<br>";

    $servicosDestaque = $servicoDAO->findAll();
    echo "✅ Dados carregados: " . count($servicosDestaque) . " serviços<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
}
