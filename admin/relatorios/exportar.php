<?php
require_once '../../shared/config/auth.php';
require_once '../../shared/dao/ContratacaoDAO.php';

Auth::requireAuth();

$tipo = $_GET['tipo'] ?? 'pdf';
$periodo = $_GET['periodo'] ?? 'mensal';

$contratacaoDAO = new ContratacaoDAO();
$estatisticas = $contratacaoDAO->getStatistics();

// Get period data
$data_inicio = null;
$data_fim = null;

if ($periodo === 'mensal') {
    $data_inicio = date('Y-m-01');
    $data_fim = date('Y-m-t');
} elseif ($periodo === 'anual') {
    $data_inicio = date('Y-01-01');
    $data_fim = date('Y-12-31');
}

$relatorio_detalhado = $contratacaoDAO->getFinancialReport($data_inicio, $data_fim);

if ($tipo === 'pdf') {
    // Simulate PDF generation (in a real implementation, you'd use a library like TCPDF or FPDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="relatorio_financeiro_' . $periodo . '_' . date('Y-m-d') . '.pdf"');
    
    echo "%PDF-1.4\n";
    echo "1 0 obj\n";
    echo "<<\n";
    echo "/Type /Catalog\n";
    echo "/Pages 2 0 R\n";
    echo ">>\n";
    echo "endobj\n";
    echo "2 0 obj\n";
    echo "<<\n";
    echo "/Type /Pages\n";
    echo "/Kids [3 0 R]\n";
    echo "/Count 1\n";
    echo ">>\n";
    echo "endobj\n";
    echo "3 0 obj\n";
    echo "<<\n";
    echo "/Type /Page\n";
    echo "/Parent 2 0 R\n";
    echo "/MediaBox [0 0 612 792]\n";
    echo "/Contents 4 0 R\n";
    echo ">>\n";
    echo "endobj\n";
    echo "4 0 obj\n";
    echo "<<\n";
    echo "/Length 100\n";
    echo ">>\n";
    echo "stream\n";
    echo "BT\n";
    echo "/F1 12 Tf\n";
    echo "50 750 Td\n";
    echo "(Relatorio Financeiro - " . ucfirst($periodo) . ") Tj\n";
    echo "ET\n";
    echo "endstream\n";
    echo "endobj\n";
    echo "xref\n";
    echo "0 5\n";
    echo "0000000000 65535 f \n";
    echo "0000000009 00000 n \n";
    echo "0000000058 00000 n \n";
    echo "0000000115 00000 n \n";
    echo "0000000200 00000 n \n";
    echo "trailer\n";
    echo "<<\n";
    echo "/Size 5\n";
    echo "/Root 1 0 R\n";
    echo ">>\n";
    echo "startxref\n";
    echo "300\n";
    echo "%%EOF\n";
    
} elseif ($tipo === 'excel') {
    // Generate CSV (Excel-compatible)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_financeiro_' . $periodo . '_' . date('Y-m-d') . '.csv"');
    
    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    // CSV Headers
    echo "Data,Total Contratos,Receita Diaria,Ticket Medio\n";
    
    // CSV Data
    foreach ($relatorio_detalhado as $linha) {
        echo sprintf(
            "%s,%d,R$ %.2f,R$ %.2f\n",
            $linha['data'],
            $linha['total_contratos'],
            $linha['receita_diaria'],
            $linha['ticket_medio']
        );
    }
    
} else {
    // Invalid type - redirect back to financial page
    header('Location: financeiro.php');
    exit;
}
?> 