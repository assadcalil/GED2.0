<?php
// Arquivo: /ged2.0/controllers/buscar_empresa.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar se as configurações já foram incluídas
if (!defined('ROOT_DIR')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/auth.php';
    require_once __DIR__ . '/../config/log.php';
}

// Verificar autenticação
Auth::requireLogin();

// Verificar se o código da empresa foi fornecido
$codigo = filter_input(INPUT_GET, 'codigo', FILTER_SANITIZE_STRING);

if (empty($codigo)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Código da empresa não fornecido.'
    ]);
    exit;
}

try {
    // Consultar empresa no banco de dados diretamente
    $sql = "SELECT id, emp_code, emp_name, emp_cnpj FROM empresas WHERE emp_code = ?";
    $empresa = Database::select($sql, [$codigo]);
    
    if (!empty($empresa)) {
        // Registrar atividade no log
        Logger::activity('empresa', "Consultou empresa por código: {$codigo}");
        
        // Formatar resposta como JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $empresa[0]
        ]);
    } else {
        // Resposta para empresa não encontrada
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Empresa não encontrada com o código informado.'
        ]);
    }
    
} catch (Exception $e) {
    // Resposta para erro do sistema
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ocorreu um erro ao buscar a empresa.',
        'details' => $e->getMessage()
    ]);
    
    // Registrar erro no log
    Logger::error('empresa', "Erro ao buscar empresa por código: " . $e->getMessage());
}
?>