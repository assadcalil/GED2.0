<?php
/**
 * Controller para gerenciamento de certificados digitais
 * Processa ações como criar, atualizar, excluir e gerar PDF de certificados
 */

// Verificar se as configurações já foram incluídas
if (!defined('ROOT_DIR')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/auth.php';
    require_once __DIR__ . '/../config/log.php';
}

// Verificar autenticação
Auth::requireLogin();

// Inicializar variáveis de resposta
$mensagem = '';
$tipo = '';

// Determinar a ação a ser executada
$acao = isset($_POST['acao']) ? $_POST['acao'] : (isset($_GET['acao']) ? $_GET['acao'] : '');

// Log da ação solicitada
Logger::debug("Ação solicitada no certificados_controller: " . $acao, $_POST);

// Processar a ação
switch ($acao) {
    case 'atualizar':
        atualizarCertificado();
        break;
    
    case 'gerar_pdf':
        gerarPDF();
        break;
    
    case 'remover_arquivo':
        removerArquivo();
        break;
    
    default:
        $mensagem = 'Ação não reconhecida';
        $tipo = 'danger';
        redirecionarComMensagem($mensagem, $tipo, '/ged2.0/views/certificates/');
        break;
}

/**
 * Atualiza os dados de um certificado digital existente
 */
function atualizarCertificado() {
    // Validar dados recebidos
    if (!isset($_POST['certificado_id']) || !isset($_POST['empresa_id']) || !isset($_POST['tipo_certificado'])) {
        $mensagem = 'Dados incompletos para atualização do certificado';
        $tipo = 'danger';
        redirecionarComMensagem($mensagem, $tipo, '/ged2.0/views/certificates/');
        return;
    }
    
    // Obter dados do formulário
    $certificadoId = (int)$_POST['certificado_id'];
    $empresaId = (int)$_POST['empresa_id'];
    $tipoCertificado = $_POST['tipo_certificado'];
    $certificadoEmissao = converterDataParaMySQL($_POST['certificado_emissao']);
    $certificadoValidade = converterDataParaMySQL($_POST['certificado_validade']);
    $certificadoSituacao = $_POST['certificado_situacao'];
    $certificadoTitular = isset($_POST['certificado_titular']) ? $_POST['certificado_titular'] : '';
    
    // Buscar o emp_code da empresa
    try {
        $sqlEmpresa = "SELECT emp_code FROM empresas WHERE id = ?";
        $empresa = Database::selectOne($sqlEmpresa, [$empresaId]);
        $empCode = $empresa ? $empresa['emp_code'] : '';
    } catch (Exception $e) {
        $empCode = '';
        Logger::warning("Erro ao buscar emp_code da empresa: " . $e->getMessage(), ['empresa_id' => $empresaId]);
    }
    
    // Verificar se a categoria do certificado deve ser derivada do tipo
    $categoriaCertificado = '';
    if (strpos($tipoCertificado, 'e-CNPJ') !== false) {
        $categoriaCertificado = 'E-CNPJ';
    } elseif (strpos($tipoCertificado, 'e-CPF') !== false) {
        $categoriaCertificado = 'E-CPF';
    } else {
        $categoriaCertificado = $tipoCertificado;
    }
    
    // Verificar se o tipo é A1 ou A3
    $tipoCertificadoEnum = 'A1'; // Valor padrão
    if (strpos($tipoCertificado, 'A3') !== false) {
        $tipoCertificadoEnum = 'A3';
    }
    
    try {
        // Preparar a consulta SQL
        $sql = "UPDATE certificado_digital SET 
                empresa_id = :empresa_id,
                emp_code = :emp_code,
                certificado_tipo = :certificado_tipo,
                certificado_categoria = :certificado_categoria,
                certificado_emissao = :certificado_emissao,
                certificado_validade = :certificado_validade,
                certificado_situacao = :certificado_situacao,
                certificado_titular = :certificado_titular,
                updated_at = NOW()
                WHERE certificado_id = :certificado_id";
        
        // Preparar os parâmetros
        $params = [
            ':empresa_id' => $empresaId,
            ':emp_code' => $empCode,
            ':certificado_tipo' => $tipoCertificadoEnum,
            ':certificado_categoria' => $categoriaCertificado,
            ':certificado_emissao' => $certificadoEmissao,
            ':certificado_validade' => $certificadoValidade,
            ':certificado_situacao' => $certificadoSituacao,
            ':certificado_titular' => $certificadoTitular,
            ':certificado_id' => $certificadoId
        ];
        
        // Registrar detalhes para debug
        Logger::debug("Parâmetros para atualização de certificado", $params);
        
        // Executar a consulta
        $resultado = Database::execute($sql, $params);
        
        // Registrar a atualização
        $detalhes = [
            'certificado_id' => $certificadoId,
            'empresa_id' => $empresaId,
            'emp_code' => $empCode,
            'tipo' => $tipoCertificadoEnum,
            'categoria' => $categoriaCertificado,
            'emissao' => $certificadoEmissao,
            'validade' => $certificadoValidade,
            'situacao' => $certificadoSituacao
        ];
        
        // Registrar a atualização no log
        Logger::database('update', 'certificado_digital', $certificadoId, 'Atualização de certificado digital', $detalhes);
        
        // Registrar na tabela de histórico se existir
        registrarHistorico($certificadoId, 'atualização', 'Dados do certificado atualizados');
        
        // Mensagem de sucesso
        $mensagem = 'Certificado digital atualizado com sucesso!';
        $tipo = 'success';
        
    } catch (PDOException $e) {
        // Registrar erro
        Logger::error("Erro ao atualizar certificado: " . $e->getMessage(), [
            'certificado_id' => $certificadoId,
            'dados' => $_POST,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Mensagem de erro
        $mensagem = 'Erro ao atualizar certificado: ' . $e->getMessage();
        $tipo = 'danger';
    }
    
    // Redirecionar de volta ao formulário com mensagem
    redirecionarComMensagem($mensagem, $tipo, "/ged2.0/views/certificates/edit.php?id=$certificadoId");
}

/**
 * Gera um PDF do certificado para impressão ou download
 */
function gerarPDF() {
    // Verificar se o ID foi fornecido
    if (!isset($_GET['id'])) {
        $mensagem = 'ID do certificado não informado';
        $tipo = 'danger';
        redirecionarComMensagem($mensagem, $tipo, '/ged2.0/views/certificates/');
        return;
    }
    
    $certificadoId = (int)$_GET['id'];
    
    // Buscar dados do certificado
    $sql = "SELECT cd.*, e.emp_name, e.emp_code, e.emp_cnpj 
            FROM certificado_digital cd 
            INNER JOIN empresas e ON cd.empresa_id = e.id 
            WHERE cd.certificado_id = ?";
    
    $certificado = Database::selectOne($sql, [$certificadoId]);
    
    if (!$certificado) {
        $mensagem = 'Certificado não encontrado';
        $tipo = 'danger';
        redirecionarComMensagem($mensagem, $tipo, '/ged2.0/views/certificates/');
        return;
    }
    
    // Registrar a geração do PDF
    Logger::activity('gerar_pdf', "Geração de PDF do certificado #$certificadoId");
    
    // Aqui você pode implementar a geração do PDF
    // Exemplo com mPDF ou outra biblioteca de PDF
    
    // Por enquanto, apenas redireciona com uma mensagem (implementação completa seria necessária)
    $mensagem = 'Função de geração de PDF ainda não implementada';
    $tipo = 'warning';
    redirecionarComMensagem($mensagem, $tipo, "/ged2.0/views/certificates/edit.php?id=$certificadoId");
}

/**
 * Remove um arquivo associado ao certificado
 */
function removerArquivo() {
    // Verificar dados recebidos
    if (!isset($_POST['arquivo_id']) || !isset($_POST['certificado_id'])) {
        $mensagem = 'Dados incompletos para remoção do arquivo';
        $tipo = 'danger';
        redirecionarComMensagem($mensagem, $tipo, '/ged2.0/views/certificates/');
        return;
    }
    
    $arquivoId = (int)$_POST['arquivo_id'];
    $certificadoId = (int)$_POST['certificado_id'];
    
    try {
        // Buscar informações do arquivo primeiro
        $sqlBuscar = "SELECT * FROM certificado_arquivos WHERE id = ? AND certificado_id = ?";
        $arquivo = Database::selectOne($sqlBuscar, [$arquivoId, $certificadoId]);
        
        if (!$arquivo) {
            $mensagem = 'Arquivo não encontrado';
            $tipo = 'danger';
            redirecionarComMensagem($mensagem, $tipo, "/ged2.0/views/certificates/edit.php?id=$certificadoId");
            return;
        }
        
        // Remover arquivo do sistema de arquivos se existir
        $caminhoArquivo = ROOT_PATH . '/uploads/certificados/' . $arquivo['arquivo_nome_sistema'];
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo);
        }
        
        // Remover do banco de dados
        $sqlRemover = "DELETE FROM certificado_arquivos WHERE id = ?";
        Database::execute($sqlRemover, [$arquivoId]);
        
        // Registrar a remoção
        Logger::activity('remover_arquivo', "Remoção de arquivo do certificado #$certificadoId: " . $arquivo['arquivo_nome_original']);
        
        // Registrar histórico
        registrarHistorico($certificadoId, 'remoção de arquivo', 'Arquivo removido: ' . $arquivo['arquivo_nome_original']);
        
        $mensagem = 'Arquivo removido com sucesso';
        $tipo = 'success';
        
    } catch (Exception $e) {
        // Registrar erro
        Logger::error("Erro ao remover arquivo: " . $e->getMessage(), [
            'arquivo_id' => $arquivoId,
            'certificado_id' => $certificadoId,
            'trace' => $e->getTraceAsString()
        ]);
        
        $mensagem = 'Erro ao remover arquivo: ' . $e->getMessage();
        $tipo = 'danger';
    }
    
    redirecionarComMensagem($mensagem, $tipo, "/ged2.0/views/certificates/edit.php?id=$certificadoId");
}

/**
 * Registra uma entrada no histórico do certificado
 */
function registrarHistorico($certificadoId, $tipoAcao, $descricao) {
    try {
        // Verificar se a tabela de histórico existe
        $checkTable = "SHOW TABLES LIKE 'certificado_historico'";
        $result = Database::select($checkTable);
        
        if (empty($result)) {
            // Tabela não existe, apenas loggar e retornar
            Logger::debug("Tabela certificado_historico não existe. Histórico não registrado.", [
                'certificado_id' => $certificadoId,
                'acao' => $tipoAcao,
                'descricao' => $descricao
            ]);
            return;
        }
        
        // Obter ID do usuário logado
        $usuarioId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Inserir no histórico
        $sql = "INSERT INTO certificado_historico 
                (certificado_id, usuario_id, tipo_acao, descricao, data_alteracao) 
                VALUES (?, ?, ?, ?, NOW())";
        
        Database::execute($sql, [
            $certificadoId,
            $usuarioId,
            $tipoAcao,
            $descricao
        ]);
        
    } catch (Exception $e) {
        // Apenas registrar o erro, mas não impedir o fluxo principal
        Logger::warning("Erro ao registrar histórico: " . $e->getMessage(), [
            'certificado_id' => $certificadoId,
            'acao' => $tipoAcao,
            'descricao' => $descricao
        ]);
    }
}

/**
 * Converte uma data no formato DD/MM/AAAA para o formato MySQL (AAAA-MM-DD)
 */
function converterDataParaMySQL($data) {
    if (empty($data)) return null;
    
    // Verificar se a data já está no formato AAAA-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return $data;
    }
    
    // Converter do formato DD/MM/AAAA para AAAA-MM-DD
    $partes = explode('/', $data);
    if (count($partes) === 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    
    // Retornar null se o formato for inválido
    return null;
}

/**
 * Redireciona para uma URL com mensagem na sessão
 */
function redirecionarComMensagem($mensagem, $tipo, $url) {
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipo'] = $tipo;
    header('Location: ' . $url);
    exit;
}