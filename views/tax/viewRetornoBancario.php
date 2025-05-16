<?php
// Definir diretório raiz para includes (definição única)
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(dirname(dirname(__FILE__))));
}

// Definir ROOT_PATH para compatibilidade com código existente
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', ROOT_DIR);
}

// Configurações para exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Sistema Contabilidade Estrela 2.0
 * Processamento de Retorno Bancário
 */

// Incluir arquivos necessários
require_once ROOT_DIR . '/app/Config/App.php';
require_once ROOT_DIR . '/app/Config/Database.php';
require_once ROOT_DIR . '/app/Config/Auth.php';
require_once ROOT_DIR . '/app/Config/Logger.php';
require_once ROOT_DIR . '/app/Dao/ImpostoDao.php';
require_once ROOT_DIR . '/app/Config/Email.php';

// Verificar autenticação e permissão
Auth::requireLogin();

// Apenas administradores podem acessar a listagem geral de impostos
if (!Auth::isAdmin()) {
    header('Location: /Ged2.0/views/errors/access-denied.php');
    exit;
}

// Inicializar variáveis
$impostoDAO = new ImpostoDAO();
$message = '';
$messageType = '';
$uploadedFile = null;
$processedPayments = [];
$totalProcessed = 0;

// Configurações de paths
$retornoPath = ROOT_PATH . '/RetornoCaixa/';
$processedLogFile = $retornoPath . 'processed_files.log';
$processedDataFile = $retornoPath . 'documentos_processados.json';

// Garantir que os diretórios existem
if (!is_dir($retornoPath)) {
    mkdir($retornoPath, 0777, true);
}

// Adicionar informações de depuração
$debug_info = [
    'diretorio_retorno' => [
        'caminho' => $retornoPath,
        'existe' => is_dir($retornoPath) ? 'Sim' : 'Não',
        'permissao_escrita' => is_writable($retornoPath) ? 'Sim' : 'Não'
    ],
    'arquivo_log' => [
        'caminho' => $processedLogFile,
        'existe' => file_exists($processedLogFile) ? 'Sim' : 'Não',
        'tamanho' => file_exists($processedLogFile) ? filesize($processedLogFile) . ' bytes' : 'N/A'
    ]
];

/**
 * Obter configurações de email do sistema
 * @return array Configurações de email
 */
function getEmailConfig() {
    // Verificar se existe um arquivo de configuração
    $config_file = ROOT_DIR . '/app/Config/email_config.php';
    
    if (file_exists($config_file)) {
        include $config_file;
        return $email_config ?? getDefaultEmailConfig();
    }
    
    return getDefaultEmailConfig();
}

/**
 * Configurações padrão de email
 * @return array Configurações padrão
 */
function getDefaultEmailConfig() {
    return [
        'notifications_enabled' => true,     // Habilitar/desabilitar notificações
        'use_email_queue' => false,          // Usar fila de emails
        'smtp_host' => 'smtp.gmail.com',
        'smtp_username' => 'recuperacaoestrela@gmail.com',
        'smtp_password' => 'sgyrmsgdaxiqvupb', // Deveria usar variável de ambiente
        'smtp_encryption' => 'ssl',
        'smtp_port' => 465,
        'from_email' => 'recuperacaoestrela@gmail.com',
        'from_name' => 'Contabilidade Estrela',
        'template_dir' => ROOT_DIR . '/templates/emails'
    ];
}

// Funções auxiliares
function php_fnumber($var1) {
    return number_format($var1, 2, ',', '.');
}

function datasql($data1) {
    $data1 = substr($data1, 0, 2) . '/' . substr($data1, 2, 2) . '/' . substr($data1, 4, 4);
    if (!empty($data1)) {
        $p_dt = explode('/', $data1);
        $data_sql = $p_dt[2] . '-' . $p_dt[1] . '-' . $p_dt[0];
        return $data_sql;
    }
    return null;
}

function datacx_databr($var1) {
    // Converter uma string data brasileira em uma data brasileira com as barras
    // Entrada: DDMMAAAA / Saida: DD/MM/AAAA
    $j_dia = substr($var1, 0, 2);
    $j_mes = substr($var1, 2, 2);
    $j_ano = substr($var1, 4, 4);
    $j_dtf = $j_dia . "/" . $j_mes . "/" . $j_ano;
    return $j_dtf;
}

function remove_zero_esq($var1) {
    $tam = strlen($var1);
    for ($i = 0; $i < $tam; $i++) {
        if (substr($var1, $i, 1) == "0") {
            $y = substr($var1, ($i + 1), ($tam));
        } else {
            return substr($var1, $i);
        }
    }
    return "0";
}

function numero_usa($var1) {
    $tam  = strlen($var1);
    $ped1 = substr($var1, 0, ($tam - 2));
    $ped2 = substr($var1, -2);
    $num2 = $ped1 . "." . $ped2;
    if ($num2 == ".") {
        $num2 = "0.00";
    }
    return $num2;
}

function motivo_liquidacao($var1) {
    $xfra = "";
    switch ($var1) {
        case "01": $xfra = " "; break;
        case "02": $xfra = "PG CASA LOTERICA"; break;
        case "03": $xfra = "PG AGENCIA CAIXA"; break;
        case "04": $xfra = "COMPENSACAO ELETRONICA"; break;
        case "05": $xfra = "COMPENSACAO CONVENCIONAL"; break;
        case "06": $xfra = "INTERNET BANKING"; break;
        case "07": $xfra = "CORRESPONDENTE BANCARIO"; break;
        case "08": $xfra = "EM CARTORIO"; break;
        case "61": $xfra = "PIX CAIXA"; break;
        case "62": $xfra = "PIX OUTROS BANCOS"; break;
        default: $xfra = "MOTIVO PG: " . $var1 . " (CONSULTAR MANUAL)"; break;
    }
    return ($xfra);
}

function motivo_rejeicao($var1) {
    $xfra = "";
    switch ($var1) {
        case "08": $xfra = "NOSSO NUMERO INVALIDO"; break;
        case "09": $xfra = "NOSSO NUMERO DUPLICADO"; break;
        case "48": $xfra = "CEP INVALIDO"; break;
        case "49": $xfra = "CEP SEM PRACA DE COBRANCA (NAO LOCALIZADO)"; break;
        case "50": $xfra = "CEP REFERENTE A UM BANCO CORRESPONDENTE"; break;
        case "51": $xfra = "CEP INCOMPATIVEL COM A UNIDADE DA FEDERACAO"; break;
        case "52": $xfra = "UNIDADE DA FEDERACAO INVALIDA"; break;
        case "87": $xfra = "NUMERO DA REMESSA INVALIDO"; break;
        case "63": $xfra = "ENTRADA PARA TITULO JA CADASTRADO"; break;
        case "16": $xfra = "DATA DE VENCIMENTO INVALIDA"; break;
        case "10": $xfra = "CARTEIRA INVALIDA"; break;
        case "06": $xfra = "NUMERO INSCRICAO DO BENEFICIARIO INVALIDO"; break;
        case "07": $xfra = "AG/CONTA/DV INVALIDOS"; break;
        default: $xfra = "ERRO: " . $var1 . " "; break;
    }
    return ($xfra);
}



// Controle de arquivos processados
function loadProcessedFiles($logFile) {
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    return [];
}

function saveProcessedFile($logFile, $filename, $fileContent, $totalProcessado, $totalValor) {
    $processed = loadProcessedFiles($logFile);
    
    // Criar hash do conteúdo para verificação única
    $fileHash = md5($fileContent);
    
    $processed[$filename] = [
        'processed_at' => date('Y-m-d H:i:s'),
        'file_hash' => $fileHash,
        'status' => 'processed',
        'user' => isset($_SESSION['user_type']) ? $_SESSION['user_type'] . ': ' . ($_SESSION['user'] ?? $_SESSION['username'] ?? 'Sistema') : 'Sistema',
        'total_registros' => $totalProcessado,
        'total_valor' => $totalValor
    ];
    
    // Adicionar também pelo hash para verificação cruzada
    $processed[$fileHash] = $processed[$filename];
    
    file_put_contents($logFile, json_encode($processed, JSON_PRETTY_PRINT));
    
    return $processed[$filename];
}

function isFileProcessed($logFile, $filename, $fileContent = null) {
    $processed = loadProcessedFiles($logFile);
    
    // Verifica pelo nome do arquivo
    if (isset($processed[$filename])) {
        return $processed[$filename];
    }
    
    // Verifica pelo hash do conteúdo (se fornecido)
    if ($fileContent) {
        $fileHash = md5($fileContent);
        if (isset($processed[$fileHash])) {
            return $processed[$fileHash];
        }
    }
    
    return false;
}

// Processar arquivo de retorno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    
    // Verificar erros no upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $messageType = 'danger';
        switch ($arquivo['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'O arquivo é muito grande.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'O upload do arquivo foi feito parcialmente.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'Nenhum arquivo foi enviado.';
                break;
            default:
                $message = 'Erro desconhecido no upload: código ' . $arquivo['error'];
        }
    } 
    // Verificar extensão
    elseif (pathinfo($arquivo['name'], PATHINFO_EXTENSION) != 'ret') {
        $messageType = 'danger';
        $message = 'O arquivo deve ter a extensão .ret';
    } 
    // Verificar se já foi processado
    else {
        // Verificar se é possível ler o arquivo
        if (!is_readable($arquivo['tmp_name'])) {
            $messageType = 'danger';
            $message = 'Não foi possível ler o arquivo temporário. Verifique as permissões.';
        } else {
            $fileContent = file_get_contents($arquivo['tmp_name']);
            $processingInfo = isFileProcessed($processedLogFile, $arquivo['name'], $fileContent);
            
            if ($processingInfo) {
                $messageType = 'warning';
                $processedTime = date('d/m/Y H:i:s', strtotime($processingInfo['processed_at']));
                $message = "ATENÇÃO: Este arquivo já foi processado anteriormente em {$processedTime}.<br>"
                         . "Nome do arquivo: <strong>" . htmlspecialchars($arquivo['name']) . "</strong>";
                
                if (isset($processingInfo['total_registros']) && isset($processingInfo['total_valor'])) {
                    $message .= "<br>Total processado: " . $processingInfo['total_registros'] 
                             . " registros | R$ " . number_format($processingInfo['total_valor'], 2, ',', '.');
                }
            } 
            else {
                // Salvar arquivo com nome único para processamento
                $nameRetorno = md5(date('Y-m-dH:i:s') . rand(10000, 99999)) . '.ret';
                $uploadPath = $retornoPath . $nameRetorno;
                
                if (move_uploaded_file($arquivo['tmp_name'], $uploadPath)) {
                    // Processar o arquivo
                    $processResult = processarArquivoRetorno($uploadPath, $arquivo['name']);
                    
                    if ($processResult['success']) {
                        $messageType = 'success';
                        $message = "Arquivo processado com sucesso! Foram processados " . $processResult['totalProcessed'] 
                                 . " pagamentos, totalizando R$ " . number_format($processResult['totalAmount'], 2, ',', '.');
                        
                        // Registrar o arquivo como processado
                        saveProcessedFile($processedLogFile, $arquivo['name'], $fileContent, $processResult['totalProcessed'], $processResult['totalAmount']);
                        
                        // Atribuir resultados para exibir na tela
                        $processedPayments = $processResult['payments'];
                        $totalProcessed = $processResult['totalAmount'];
                        $uploadedFile = $arquivo['name'];

                        // Armazenar na sessão para uso posterior na impressão
                        $_SESSION['last_processed_payments'] = $processResult['payments'];
                        $_SESSION['last_processed_total'] = $processResult['totalAmount'];

                        // Opcional: Armazenar também em um arquivo temporário para redundância
                        $jsonData = json_encode([
                            'payments' => $processResult['payments'],
                            'total' => $processResult['totalAmount']
                        ]);
                        file_put_contents($retornoPath . 'documentos_processados.json', $jsonData);
                        
                        // Registrar no log de atividades
                        if (class_exists('Logger')) {
                            Logger::activity('financeiro', "Processou arquivo de retorno: {$arquivo['name']} - Total: R$ " . number_format($processResult['totalAmount'], 2, ',', '.'));
                        }
                    } else {
                        $messageType = 'danger';
                        $message = "Erro ao processar o arquivo: " . $processResult['error'];
                        
                        // Remover arquivo em caso de erro
                        @unlink($uploadPath);
                        
                        // Registrar no log de erros
                        if (class_exists('Logger')) {
                            Logger::activity('erro', "Erro ao processar arquivo de retorno {$arquivo['name']}: " . $processResult['error']);
                        }
                    }
                } else {
                    $messageType = 'danger';
                    $message = "Erro ao salvar o arquivo. Verifique as permissões do diretório: " . $retornoPath;
                }
            }
        }
    }
}

// Função para processar o arquivo de retorno
function processarArquivoRetorno($filePath, $originalFilename) {
    global $impostoDAO;
    
    $result = [
        'success' => false,
        'error' => '',
        'totalProcessed' => 0,
        'totalAmount' => 0,
        'payments' => []
    ];
    
    // Abrir arquivo
    $lendo = @fopen($filePath, "r");
    if (!$lendo) {
        $result['error'] = "Erro ao abrir o arquivo. Verifique se o arquivo existe e tem permissões de leitura.";
        return $result;
    }
    
    $i = 1;
    $total_itens_processados = 0;
    $total_recebido = 0;
    $pagamentos = [];
    $b = 4; // Offset para leitura
    
    // Variáveis para segmentos T e U
    $nosso_numero_alex = null;
    $nosso_numero_caixa = null;
    $nosso_num = null;
    $vencimento = null;
    $valor_nominal = null;
    $cod_movimento = null;
    $xfrase_movimento = null;
    $bg_color = null;
    $frase_motivo = null;
    
    // Ler arquivo linha por linha
    while (!feof($lendo)) {
        $linha = fgets($lendo, 241);
        
        // Verificar se a linha é válida
        if (!$linha) {
            continue;
        }
        
        $rr = "<pre>" . $linha . "</pre>";
        $xtamanho_linha = strlen($linha);
        
        if ($xtamanho_linha == 240) {
            // Processa linha segmento T
            if ($i > 2 && substr($rr, $b + 14, 1) == "T" && substr($rr, $b + 16, 2) != 28) {
                $num_sequencial_t       = substr($rr, $b + 9, 5);
                $modalidade_nosso_numero = substr($rr, $b + 40, 2);
                $nosso_numero_caixa     = substr($rr, $b + 42, 15);
                $nosso_num              = substr($rr, $b + 43, 14);
                $nosso_numero_alex      = remove_zero_esq($nosso_num);
                $vencimento             = substr($rr, $b + 74, 8);
                $vm                     = substr($rr, $b + 82, 15);
                $valor_nominal          = numero_usa(remove_zero_esq($vm));
                $cod_movimento          = substr($rr, $b + 16, 2);
                
                switch ($cod_movimento) {
                    case "06":
                        $xfrase_movimento = "TITULO LIQUIDADO";
                        $bg_color = "#98FB98"; // verde
                        $cod_motivo_liquidacao = substr($rr, $b + 214, 10);
                        $frase_motivo = motivo_liquidacao(substr(trim($cod_motivo_liquidacao), -2));
                        break;
                    case "02":
                        $xfrase_movimento = "REMESSA ENTRADA CONFIRMADA";
                        $bg_color = "#FFF"; // branco
                        break;
                    case "03":
                        $xfrase_movimento = "REMESSA ENTRADA REJEITADA";
                        $bg_color = "#FFC4C4"; // vermelho
                        $cod_motivo_rejeicao = substr($rr, $b + 214, 10);
                        $frase_motivo = motivo_rejeicao(substr(trim($cod_motivo_rejeicao), -2));
                        break;
                    case "28":
                        $xfrase_movimento = "DEBITO DE TARIFAS/CUSTAS";
                        break;
                    case "27":
                        $xfrase_movimento = "CONFIRMACAO DO PEDIDO DE ALTERACAO OUTROS DADOS";
                        break;
                    case "30":
                        $xfrase_movimento = "ALTERACAO DE DADOS REJEITADA";
                        break;
                    case "45":
                        $xfrase_movimento = "ALTERACAO DE DADOS";
                        break;
                }
            }
            
            // Processa linha segmento U
            if ($i > 3 && substr($rr, $b + 14, 1) == "U" && substr($rr, $b + 16, 2) != 28) {
                $total_itens_processados++;
                
                $cod_movimento_u         = $cod_movimento;
                $num_sequencial_u        = substr($rr, $b + 9, 5);
                $jumu                    = substr($rr, $b + 18, 15);
                $juros_multa             = numero_usa(remove_zero_esq($jumu));
                $desco                   = substr($rr, $b + 33, 15);
                $desconto                = numero_usa(remove_zero_esq($desco));
                $vp                      = substr($rr, $b + 78, 15);
                $valor_pago              = numero_usa(remove_zero_esq($vp));
                $vl                      = substr($rr, $b + 93, 15);
                $valor_liquido           = numero_usa(remove_zero_esq($vl));
                $outdes                  = substr($rr, $b + 108, 15);
                $outras_despesas         = numero_usa(remove_zero_esq($outdes));
                $data_ocorrencia         = substr($rr, $b + 138, 8);
                $data_credito            = substr($rr, $b + 146, 8);
                $data_deb_tarifa         = substr($rr, $b + 158, 8);
                
                if ($cod_movimento_u == "06") { // título liquidado (pago)
                    // Pegando o ID do imposto
                    $id_imposto = '';
                    if (!empty($nosso_numero_alex) && strlen($nosso_numero_alex) > 4) {
                        $id_imposto = remove_zero_esq(substr($nosso_numero_alex, 4, 10));
                    } else {
                        // Log de problema com nosso número
                        if (class_exists('Logger')) {
                            Logger::activity('erro', "Erro ao processar Nosso Número: Formato inválido - " . $nosso_numero_alex);
                        }
                        continue; // Pular este registro
                    }
                    
                    // Consultando o banco de dados para pegar dados do imposto
                    try {
                        $stmt = $impostoDAO->runQuery("SELECT id, codigo, nome, cpf, usuario, valor2025 FROM impostos WHERE id=:id_imposto");
                        $stmt->execute(array(":id_imposto" => $id_imposto));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        // Log de erro na consulta
                        if (class_exists('Logger')) {
                            Logger::activity('erro', "Erro na consulta SQL: " . $e->getMessage());
                        }
                        $row = null;
                    }
                    
                    if ($row) {
                        $id_imposto = $row['id'];
                        
                        // Processar e formatar valores
                        $valor_banco1 = str_replace('.', '', php_fnumber($valor_pago));
                        $valor_banco = str_replace(',', '.', $valor_banco1);
                        
                        // Acumular o valor para o total
                        $total_recebido += (float)$valor_banco;
                        
                        // Formatar data
                        $explode_data = explode('/', datacx_databr($data_ocorrencia));
                        $data_banco = $explode_data[2] . '-' . $explode_data[1] . '-' . $explode_data[0];
                        
                        // Verificar se o valor pago está correto (com uma margem de tolerância)
                        $valor_esperado = (float)$row['valor2025'];
                        $valor_pago_float = (float)$valor_banco;
                        $diferenca = abs($valor_esperado - $valor_pago_float);
                        
                        // Obter configuração de tolerância (padrão: R$ 1,00)
                        $config = getEmailConfig();
                        $tolerancia = $config['payment_tolerance'] ?? 1.00;
                        
                        // Se a diferença for maior que a tolerância, registrar um alerta
                        $observacao = "";
                        if ($diferenca > $tolerancia) {
                            $observacao = "ALERTA: Valor pago (R$ " . number_format($valor_pago_float, 2, ',', '.') . 
                                         ") diferente do valor esperado (R$ " . number_format($valor_esperado, 2, ',', '.') . ")";
                        }
                        
                        // 1. Atualizar a tabela impostos
                            try {
                                $stmt = $impostoDAO->runQuery("
                                    UPDATE impostos 
                                    SET status_boleto_2025 = '1', 
                                        data_pagamento_2025 = :data_banco, 
                                        valor_pagamento_2025 = :valor_banco 
                                    WHERE id = :id_imposto");
                                $stmt->execute(array(
                                    ":data_banco" => $data_banco,
                                    ":valor_banco" => $valor_banco,
                                    ":id_imposto" => $id_imposto
                                ));
                                
                                // Preparar dados do cliente e pagamento para notificação por email
                                $cliente = [
                                    'codigo' => $row['codigo'],
                                    'nome' => $row['nome'],
                                    'cpf' => $row['cpf']
                                ];

                                $paymentInfo = [
                                    'data_pagamento' => datacx_databr($data_ocorrencia),
                                    'valor' => $valor_banco,
                                    'motivo' => $frase_motivo
                                ];

                                // Incluir o arquivo de função de envio de email
                                require_once 'enviar_email.php';

                                $emailSent = false;
                                $emailInfo = '';

                                // Enviar email de notificação para o email fixo
                                $emailSent = enviarEmailNotificacao($cliente, $paymentInfo);

                                // Adicionar informações sobre a notificação por email
                                $emailInfo = $emailSent 
                                    ? " | Notificação enviada para " . EmailConfig::EMAIL_COPIA 
                                    : " | Falha ao enviar notificação para " . EmailConfig::EMAIL_COPIA;

                                // Logar a tentativa de envio de email
                                if (class_exists('Logger')) {
                                    Logger::activity(
                                        'email', 
                                        "Notificação de pagamento: Cliente #{$row['codigo']} - {$row['nome']} - R$ " . 
                                        number_format($valor_banco, 2, ',', '.') . 
                                        " - Email para: " . EmailConfig::EMAIL_COPIA . " - " . 
                                        ($emailSent ? "Enviado" : "Falha")
                                    );
                                }
                                
                                // 2. Atualizar a tabela impostos_boletos
                                // Primeiro buscar o ID do boleto correspondente
                                $stmt = $impostoDAO->runQuery("
                                    SELECT id, status, linha_digitavel, observacoes  
                                    FROM impostos_boletos 
                                    WHERE imposto_id = :id_imposto AND status = 5
                                    ORDER BY created_at DESC 
                                    LIMIT 1");
                                $stmt->execute(array(":id_imposto" => $id_imposto));
                                $boleto = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $boleto_atualizado = false;
                                if ($boleto) {
                                    // Atualizar o boleto correspondente
                                    $novaObservacao = trim(($boleto['observacoes'] ? $boleto['observacoes'] . ' | ' : '') . $observacao . 
                                                    ' | Pagamento via retorno bancário. Arquivo: ' . $originalFilename .
                                                    ' | Processado em: ' . date('d/m/Y H:i:s') . 
                                                    ' | Tipo: ' . $frase_motivo . $emailInfo);
                                    
                                    $stmt = $impostoDAO->runQuery("
                                        UPDATE impostos_boletos 
                                        SET status = 1, 
                                            observacoes = :observacoes,
                                            updated_at = NOW() 
                                        WHERE id = :id_boleto");
                                    $stmt->execute(array(
                                        ":observacoes" => $novaObservacao,
                                        ":id_boleto" => $boleto['id']
                                    ));
                                    
                                    $boleto_atualizado = true;
                                }
                                
                                // Adicionar ao array de pagamentos processados
                                $pagamentos[] = [
                                    'id' => $row['id'],
                                    'codigo' => $row['codigo'],
                                    'nome' => $row['nome'],
                                    'cpf' => $row['cpf'],
                                    'valor' => $valor_banco,
                                    'valor_formatado' => 'R$ ' . number_format($valor_banco, 2, ',', '.'),
                                    'data_pagamento' => datacx_databr($data_ocorrencia),
                                    'data_credito' => datacx_databr($data_credito),
                                    'motivo' => $frase_motivo,
                                    'boleto_atualizado' => $boleto_atualizado,
                                    'observacao' => $observacao,
                                    'status' => 'success',
                                    'email_sent' => $emailSent ?? false,
                                    'email_recipient' => EmailConfig::EMAIL_COPIA
                                ];          
                            } catch (Exception $e) {
                                // Adicionar ao array de pagamentos com erro
                                $pagamentos[] = [
                                    'id' => $row['id'],
                                    'codigo' => $row['codigo'],
                                    'nome' => $row['nome'],
                                    'cpf' => $row['cpf'],
                                    'valor' => $valor_banco,
                                    'valor_formatado' => 'R$ ' . number_format($valor_banco, 2, ',', '.'),
                                    'data_pagamento' => datacx_databr($data_ocorrencia),
                                    'data_credito' => datacx_databr($data_credito),
                                    'motivo' => $frase_motivo,
                                    'boleto_atualizado' => false,
                                    'observacao' => 'ERRO: ' . $e->getMessage(),
                                    'status' => 'danger',
                                    'email_sent' => false,
                                    'email_recipient' => EmailConfig::EMAIL_COPIA
                                ];
                                
                                // Log de erro
                                if (class_exists('Logger')) {
                                    Logger::activity('erro', "Erro ao atualizar banco de dados para imposto #{$row['id']}: " . $e->getMessage());
                                }
                            }
                    } else {
                        // Imposto não encontrado na base
                        $pagamentos[] = [
                            'id' => 'N/A',
                            'codigo' => 'N/A',
                            'nome' => 'ID NÃO ENCONTRADO: ' . $id_imposto,
                            'cpf' => 'N/A',
                            'valor' => $valor_pago,
                            'valor_formatado' => 'R$ ' . number_format($valor_pago, 2, ',', '.'),
                            'data_pagamento' => datacx_databr($data_ocorrencia),
                            'data_credito' => datacx_databr($data_credito),
                            'motivo' => $frase_motivo,
                            'boleto_atualizado' => false,
                            'observacao' => 'Imposto não encontrado na base de dados',
                            'status' => 'warning',
                            'email_sent' => false,
                            'email_recipient' => EmailConfig::EMAIL_COPIA
                        ];
                        
                        // Log de imposto não encontrado
                        if (class_exists('Logger')) {
                            Logger::activity('aviso', "Imposto não encontrado: ID {$id_imposto}, Nosso Número: {$nosso_numero_alex}");
                        }
                    }
                }
            }
            $i++;
        }
    }
    
    // Fechar o arquivo
    fclose($lendo);
    
    // Atualizar resultado
    $result['success'] = true;
    $result['totalProcessed'] = count($pagamentos);
    $result['totalAmount'] = $total_recebido;
    $result['payments'] = $pagamentos;
    
    return $result;
}

// Obter histórico de arquivos processados
$historico = loadProcessedFiles($processedLogFile);

// Ordenar histórico por data de processamento (mais recente primeiro)
if (!empty($historico)) {
    uasort($historico, function($a, $b) {
        if (isset($a['processed_at']) && isset($b['processed_at'])) {
            return strtotime($b['processed_at']) - strtotime($a['processed_at']);
        }
        return 0;
    });
    
    // Limitar aos últimos 30 registros para exibição
    $historico = array_slice($historico, 0, 30, true);
}

// Filtrar histórico para remover registros de hash
$historico_filtrado = [];
foreach ($historico as $chave => $valor) {
    if (strlen($chave) !== 32 || !ctype_xdigit($chave)) {
        $historico_filtrado[$chave] = $valor;
    }
}
$historico = $historico_filtrado;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retorno Bancário - Caixa Econômica Federal - <?php echo defined('SITE_NAME') ? SITE_NAME : 'Contabilidade Estrela'; ?></title>
    
    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    
    <!-- Estilo personalizado -->
    <link rel="stylesheet" href="/GED2.0/assets/css/dashboard.css">
    <link rel="stylesheet" href="/GED2.0/assets/css/retorno-bancario.css">
    
    <style>
        /* Estilos personalizados adicionais */
        .payment-row-success {
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        .payment-row-danger {
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        .payment-row-warning {
            background-color: rgba(255, 193, 7, 0.05);
        }
        
        .result-card {
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        
        .result-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .result-body {
            padding: 1.25rem;
        }
        
        .upload-icon {
            background-color: #f8f9fa;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        /* Feedback visual durante upload */
        #fileUploadForm.uploading .upload-icon {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* Progress bar para uploads */
        .progress {
            display: none;
        }
        
        #fileUploadForm.uploading .progress {
            display: block;
        }
    </style>
</head>
<body data-user-type="<?php echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user'; ?>">
    <div class="dashboard-container">
        <!-- Menu Lateral -->
        <?php include_once ROOT_PATH . '/views/partials/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <div class="main-content">
            <!-- Cabeçalho -->
            <?php include_once ROOT_PATH . '/views/partials/header.php'; ?>
            
            <!-- Conteúdo da Página -->
            <div class="dashboard-content">
                <div class="container-fluid">
                    <!-- Cabeçalho da Página -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="page-title">Processamento de Retorno Bancário</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="viewListagemImpostos.php">Imposto de Renda</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Retorno Bancário</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-auto">
                                <a href="email_config.php" class="btn btn-outline-primary">
                                    <i class="fas fa-cog me-2"></i> Configurações de Email
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações de depuração (comentado, mas disponível para desenvolvimento) -->
                    <?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle"></i> Informações de sistema</h5>
                        <p><strong>Diretório RetornoCaixa:</strong> <?php echo $debug_info['diretorio_retorno']['caminho']; ?> (<?php echo $debug_info['diretorio_retorno']['existe']; ?> | Permissão de escrita: <?php echo $debug_info['diretorio_retorno']['permissao_escrita']; ?>)</p>
                        <p><strong>Arquivo de log:</strong> <?php echo $debug_info['arquivo_log']['caminho']; ?> (<?php echo $debug_info['arquivo_log']['existe']; ?> | <?php echo $debug_info['arquivo_log']['tamanho']; ?>)</p>
                        <p><strong>Histórico:</strong> <?php echo count($historico); ?> arquivo(s) processado(s)</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($message) && $message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Botões para navegar entre as abas manualmente -->
                    <div class="mb-3">
                        <button class="btn btn-outline-primary" onclick="mostrarAba('upload')">Upload</button>
                        <button class="btn btn-outline-primary" onclick="mostrarAba('history')">Histórico</button>
                        <?php if (!empty($processedPayments)): ?>
                        <button class="btn btn-outline-primary" onclick="mostrarAba('result')">Resultados</button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Conteúdo das Abas -->
                    <div class="tab-content">
                        <!-- Aba de Upload -->
                        <div class="tab-pane" id="upload">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>
                                        Upload de Arquivo de Retorno
                                    </h5>
                                    <p class="card-text text-muted">
                                        Faça o upload do arquivo de retorno bancário da Caixa Econômica Federal (formato .ret) para processar os pagamentos automaticamente.
                                    </p>
                                    
                                    <form id="fileUploadForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="mt-4">
                                        <div class="card mb-4">
                                            <div class="card-body text-center">
                                                <div class="upload-icon mb-3">
                                                    <i class="fas fa-file-upload fa-3x text-primary"></i>
                                                </div>
                                                <h5>Selecione o arquivo de retorno bancário</h5>
                                                <p class="text-muted mb-4">Formato aceito: arquivo .ret da Caixa Econômica Federal</p>
                                                
                                                <div class="input-group mb-3 w-75 mx-auto">
                                                    <input type="file" class="form-control" id="arquivo" name="arquivo" accept=".ret">
                                                    <button class="btn btn-primary" type="submit" id="upload-button">
                                                        <i class="fas fa-upload me-2"></i> Processar
                                                    </button>
                                                </div>
                                                
                                                <!-- Barra de progresso -->
                                                <div class="progress mt-3 w-75 mx-auto">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                                                </div>
                                                
                                                <!-- Mensagem de carregamento -->
                                                <div class="loading-message mt-2" style="display: none;">
                                                    <small class="text-muted">Processando arquivo... Por favor, aguarde.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="alert alert-info mt-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong> O sistema irá atualizar automaticamente o status dos boletos para "Pago" 
                                        com base nos registros do arquivo de retorno. Certifique-se de que o arquivo é válido e gerado pelo 
                                        sistema bancário da Caixa Econômica Federal.
                                        <br><br>
                                        <i class="fas fa-envelope me-2"></i>
                                        <strong>Notificações:</strong> As notificações por email serão enviadas para 
                                        <span class="badge bg-success"><?php echo EmailConfig::EMAIL_COPIA; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aba de Histórico -->
                        <div class="tab-pane" id="history">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-history me-2"></i>
                                        Histórico de Processamentos
                                    </h5>
                                    <p class="card-text text-muted">
                                        Veja os últimos arquivos de retorno processados no sistema.
                                    </p>
                                    
                                    <?php if (empty($historico)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Nenhum arquivo de retorno foi processado ainda.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="historicTable">
                                                <thead>
                                                    <tr>
                                                        <th>Arquivo</th>
                                                        <th>Data de Processamento</th>
                                                        <th>Usuário</th>
                                                        <th>Pagamentos</th>
                                                        <th>Valor Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($historico as $filename => $info): 
                                                        // Pular registros de hash MD5
                                                        if (strlen($filename) === 32 && ctype_xdigit($filename)) continue;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($filename); ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($info['processed_at'] ?? date('Y-m-d H:i:s'))); ?></td>
                                                        <td><?php echo htmlspecialchars($info['user'] ?? 'Sistema'); ?></td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <?php echo number_format($info['total_registros'] ?? 0, 0, ',', '.'); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                R$ <?php echo number_format($info['total_valor'] ?? 0, 2, ',', '.'); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aba de Resultados (aparece apenas após o processamento) -->
                        <?php if (!empty($processedPayments)): ?>
                        <div class="tab-pane" id="result">
                            <div class="result-card">
                                <div class="result-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">
                                                <i class="fas fa-file-invoice me-2"></i>
                                                <?php echo htmlspecialchars($uploadedFile); ?>
                                            </h5>
                                            <p class="mb-0">
                                                <i class="fas fa-calendar-check me-2"></i>
                                                Processado em <?php echo date('d/m/Y H:i'); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <h4>R$ <?php echo number_format($totalProcessed, 2, ',', '.'); ?></h4>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <?php echo count($processedPayments); ?> pagamento(s)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="result-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="paymentsTable">
                                            <thead>
                                                <tr>
                                                    <th>CÓDIGO</th>
                                                    <th>Cliente</th>
                                                    <th>CPF</th>
                                                    <th>Valor</th>
                                                    <th>Data Pgto</th>
                                                    <th>Status</th>
                                                    <th>Local</th>
                                                    <th>Notificação</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($processedPayments as $payment): ?>
                                                <tr class="payment-row-<?php echo $payment['status']; ?>">
                                                    <td><?php echo htmlspecialchars($payment['codigo']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($payment['nome']); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment['cpf']); ?></td>
                                                    <td>R$ <?php echo number_format((float)$payment['valor'], 2, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['data_pagamento']); ?></td>
                                                    <td>
                                                        <?php if ($payment['status'] === 'success'): ?>
                                                            <span class="badge bg-success">Pago</span>
                                                        <?php elseif ($payment['status'] === 'danger'): ?>
                                                            <span class="badge bg-danger">Erro</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">Atenção</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($payment['motivo']); ?>
                                                        <?php if (!empty($payment['observacao'])): ?>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($payment['observacao']); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($payment['email_sent'])): ?>
                                                            <?php if ($payment['email_sent']): ?>
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-envelope me-1"></i> Enviado
                                                                </span>
                                                                <div class="small text-muted">
                                                                    <?php echo htmlspecialchars($payment['email_recipient']); ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> Falha
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-minus me-1"></i> N/A
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <button class="btn btn-secondary" onclick="generatePDF()">
                                            <i class="fas fa-print me-2"></i> Imprimir Relatório
                                        </button>
                                        <div>
                                            <?php 
                                            $config = getEmailConfig();
                                            if (!$config['notifications_enabled']): 
                                            ?>
                                            <button class="btn btn-outline-success me-2" onclick="enviarNotificacoesManual()">
                                                <i class="fas fa-envelope me-2"></i> Enviar Notificações
                                            </button>
                                            <?php endif; ?>
                                            <a href="viewListagemImpostos.php" class="btn btn-primary">
                                                <i class="fas fa-arrow-left me-2"></i> Voltar para Listagem
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Rodapé -->
            <footer class="dashboard-footer">
                <div class="container-fluid">
                    <div class="copyright">
                        GED Contabilidade Estrela &copy; <?php echo date('Y'); ?>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <!-- Adicionar bibliotecas jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    
    <!-- Sweet Alert 2 para mensagens amigáveis -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Script personalizado -->
    <script src="/GED2.0/assets/js/dashboard.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables
        if (document.getElementById('paymentsTable')) {
            $('#paymentsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-2"></i>Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                        className: 'btn btn-danger'
                    }
                ],
                pageLength: 25
            });
        }
        
        // Inicializar DataTable para histórico
        if (document.getElementById('historicTable')) {
            $('#historicTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
                },
                pageLength: 10,
                order: [[1, 'desc']] // Ordenar por data (decrescente)
            });
        }
        
        // Mostrar a aba de upload por padrão
        mostrarAba('upload');
        
        // Se houver resultados, mostrar a aba de resultados
        <?php if (!empty($processedPayments)): ?>
            mostrarAba('result');
        <?php endif; ?>
        
        // Animação durante o upload
        const uploadForm = document.getElementById('fileUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function() {
                const fileInput = document.getElementById('arquivo');
                
                if (!fileInput.files.length) {
                    Swal.fire({
                        title: 'Aviso',
                        text: 'Por favor, selecione um arquivo para processar.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                
                const file = fileInput.files[0];
                if (file.name.split('.').pop().toLowerCase() !== 'ret') {
                    Swal.fire({
                        title: 'Erro',
                        text: 'O arquivo deve ter a extensão .ret',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                
                // Iniciar animação de carregamento
                this.classList.add('uploading');
                document.querySelector('.loading-message').style.display = 'block';
                
                // Desabilitar botão de envio para evitar múltiplos envios
                document.getElementById('upload-button').disabled = true;
                
                // Simular progresso (já que não temos um upload real via AJAX)
                const progressBar = document.querySelector('.progress-bar');
                let progress = 0;
                const interval = setInterval(function() {
                    progress += 5;
                    if (progress > 90) {
                        clearInterval(interval);
                    }
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                }, 300);
            });
        }
    });
    
    // Função para mostrar aba específica
    function mostrarAba(id) {
        // Esconder todas as abas
        document.querySelectorAll('.tab-pane').forEach(function(tab) {
            tab.style.display = 'none';
        });
        
        // Mostrar a aba selecionada
        document.getElementById(id).style.display = 'block';
        
        // Destacar o botão ativo
        document.querySelectorAll('.btn-outline-primary').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Encontrar e ativar o botão correspondente
        document.querySelectorAll('.btn-outline-primary').forEach(function(btn) {
            if (btn.getAttribute('onclick').includes(id)) {
                btn.classList.add('active');
            }
        });
    }
    
    // Função para enviar notificações manualmente
    function enviarNotificacoesManual() {
        Swal.fire({
            title: 'Enviar notificações',
            text: 'Deseja enviar notificações para todos os pagamentos processados?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar indicador de carregamento
                Swal.fire({
                    title: 'Enviando notificações',
                    text: 'Por favor, aguarde...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar requisição AJAX para enviar notificações
                $.ajax({
                    url: 'enviar_notificacoes.php',
                    type: 'POST',
                    data: {
                        payments: <?php echo json_encode($processedPayments ?? []); ?>
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            
                            if (data.success) {
                                Swal.fire({
                                    title: 'Sucesso',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Recarregar a página para atualizar status
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Aviso',
                                    text: data.message,
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                title: 'Erro',
                                text: 'Erro ao processar a resposta do servidor',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Erro',
                            text: 'Erro ao enviar requisição. Tente novamente.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
    
    // Função para gerar PDF
    function generatePDF() {
        // Mostrar indicador de carregamento
        Swal.fire({
            title: 'Gerando PDF',
            text: 'Por favor, aguarde...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Importar jsPDF
        const { jsPDF } = window.jspdf;
        
        // Criar novo documento PDF
        const doc = new jsPDF();
        
        // Adicionar cabeçalho
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('CONTABILIDADE ESTRELA', doc.internal.pageSize.width / 2, 15, { align: 'center' });
        
        doc.setFontSize(12);
        doc.setFont('helvetica', 'normal');
        doc.text('Imposto de Renda 2025', doc.internal.pageSize.width / 2, 22, { align: 'center' });
        
        // Adicionar informações do arquivo
        doc.setFontSize(10);
        doc.text('Arquivo: <?php echo htmlspecialchars($uploadedFile ?? ""); ?>', 14, 30);
        doc.text('Data do processamento: <?php echo date('d/m/Y H:i'); ?>', 14, 35);
        
        // Preparar os dados da tabela
        const tableColumn = ["Código", "Nome", "CPF", "Valor Pago", "Data Pagamento", "Status", "Local", "Notificação"];
        const tableRows = [];
        
        // Obter os dados da tabela na página
        const table = document.getElementById('paymentsTable');
        const rows = table.querySelectorAll('tbody tr');
        
        let totalValue = 0;
        
        // Extrair dados da tabela
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            
            // Extrair código e nome do cliente
            const codigo = cells[0].textContent.trim();
            const nome = cells[1].textContent.trim();
            const cpf = cells[2].textContent.trim();
            
            // Extrair valor e converter para número
            const valorText = cells[3].textContent.replace('R$', '').trim();
            const valor = parseFloat(valorText.replace('.', '').replace(',', '.'));
            totalValue += isNaN(valor) ? 0 : valor;
            
            // Extrair data de pagamento
            const dataPagamento = cells[4].textContent.trim();
            
            // Extrair status
            const statusBadge = cells[5].querySelector('.badge');
            const status = statusBadge ? statusBadge.textContent.trim() : 'N/A';
            
            // Extrair local/motivo
            const local = cells[6].textContent.trim();
            
            // Extrair informações de notificação
            const notifBadge = cells[7].querySelector('.badge');
            const notif = notifBadge ? notifBadge.textContent.trim() : 'N/A';
            
            // Adicionar linha à tabela
            tableRows.push([
                codigo,
                nome,
                cpf,
                "R$ " + valor.toFixed(2).replace('.', ','),
                dataPagamento,
                status,
                local,
                notif
            ]);
        });
        
        // Adicionar a tabela ao PDF
        doc.autoTable({
            head: [tableColumn],
            body: tableRows,
            startY: 40,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 3
            },
            headStyles: {
                fillColor: [220, 220, 220],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: {cellWidth: 20}, // Código
                1: {cellWidth: 40}, // Nome
                2: {cellWidth: 25}, // CPF
                3: {cellWidth: 20}, // Valor
                4: {cellWidth: 25}, // Data
                5: {cellWidth: 15}, // Status
                6: {cellWidth: 30}, // Local
                7: {cellWidth: 25}  // Notificação
            }
        });
        
        // Adicionar linha de total
        const finalY = doc.lastAutoTable.finalY || 40;
        
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10);
        
        // Adicionar rodapé com total
        doc.text('TOTAL GERAL RECEBIDO:', 120, finalY + 8, { align: 'right' });
        doc.text('R$ ' + totalValue.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}), 130, finalY + 8);
        
        // Adicionar informações de processamento
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        const currentDate = new Date().toLocaleString('pt-BR');
        doc.text('Processado por: Contabilidade Estrela - <?php echo isset($_SESSION["user"]) ? $_SESSION["user"] : (isset($_SESSION["username"]) ? $_SESSION["username"] : "SISTEMA"); ?> ' + currentDate, 190, finalY + 20, { align: 'right' });
        
        // Adicionar informações sobre notificações por e-mail
        doc.text('Email de Notificação: <?php echo EmailConfig::EMAIL_COPIA; ?>', 14, finalY + 25);
        
        // Salvar o PDF
        const filename = 'Retorno_Bancario_<?php echo date('Y-m-d'); ?>.pdf';
        doc.save(filename);
        
        // Fechar indicador de carregamento
        Swal.close();
    }
    </script>
    <!-- Scripts adicionais para página de configuração de email -->
    <?php if (basename($_SERVER['PHP_SELF']) == 'email_config.php'): ?>
    <script>
        // Script específico para a página de configuração de email
        document.addEventListener('DOMContentLoaded', function() {
            // Função para testar conexão SMTP
            const testSMTPBtn = document.getElementById('test-smtp-connection');
            if (testSMTPBtn) {
                testSMTPBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Coletar dados do formulário
                    const host = document.getElementById('smtp_host').value;
                    const port = document.getElementById('smtp_port').value;
                    const username = document.getElementById('smtp_username').value;
                    const password = document.getElementById('smtp_password').value;
                    const encryption = document.getElementById('smtp_encryption').value;
                    
                    // Validar campos
                    if (!host || !port || !username || !password) {
                        Swal.fire({
                            title: 'Aviso',
                            text: 'Preencha todos os campos SMTP antes de testar a conexão.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    // Mostrar indicador de carregamento
                    Swal.fire({
                        title: 'Testando conexão SMTP',
                        text: 'Por favor, aguarde...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar requisição AJAX para testar conexão
                    $.ajax({
                        url: 'test_smtp_connection.php',
                        type: 'POST',
                        data: {
                            host: host,
                            port: port,
                            username: username,
                            password: password,
                            encryption: encryption
                        },
                        success: function(response) {
                            try {
                                const data = JSON.parse(response);
                                
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Sucesso',
                                        text: 'Conexão SMTP estabelecida com sucesso!',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Erro',
                                        text: 'Falha na conexão SMTP: ' + data.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            } catch (e) {
                                Swal.fire({
                                    title: 'Erro',
                                    text: 'Erro ao processar a resposta do servidor',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Erro',
                                text: 'Erro ao enviar requisição. Tente novamente.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            }
            
            // Função para enviar email de teste
            const testEmailBtn = document.getElementById('send-test-email');
            if (testEmailBtn) {
                testEmailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Perguntar para qual email enviar o teste
                    Swal.fire({
                        title: 'Email de teste',
                        input: 'email',
                        inputLabel: 'Para qual endereço de email deseja enviar o teste?',
                        inputPlaceholder: 'Digite o email',
                        showCancelButton: true,
                        confirmButtonText: 'Enviar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Você precisa informar um email!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mostrar indicador de carregamento
                            Swal.fire({
                                title: 'Enviando email de teste',
                                text: 'Por favor, aguarde...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Enviar requisição AJAX para enviar email de teste
                            $.ajax({
                                url: 'send_test_email.php',
                                type: 'POST',
                                data: {
                                    email: result.value,
                                    host: document.getElementById('smtp_host').value,
                                    port: document.getElementById('smtp_port').value,
                                    username: document.getElementById('smtp_username').value,
                                    password: document.getElementById('smtp_password').value,
                                    encryption: document.getElementById('smtp_encryption').value,
                                    from_email: document.getElementById('from_email').value,
                                    from_name: document.getElementById('from_name').value
                                },
                                success: function(response) {
                                    try {
                                        const data = JSON.parse(response);
                                        
                                        if (data.success) {
                                            Swal.fire({
                                                title: 'Sucesso',
                                                text: 'Email de teste enviado com sucesso para ' + result.value,
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Erro',
                                                text: 'Falha ao enviar email: ' + data.message,
                                                icon: 'error',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    } catch (e) {
                                        Swal.fire({
                                            title: 'Erro',
                                            text: 'Erro ao processar a resposta do servidor',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Erro',
                                        text: 'Erro ao enviar requisição. Tente novamente.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        }
                    });
                });
            }
            
            // Processamento de emails em fila manualmente
            const processQueueBtn = document.getElementById('process-email-queue');
            if (processQueueBtn) {
                processQueueBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Confirmar processamento
                    Swal.fire({
                        title: 'Processar fila de emails',
                        text: 'Deseja processar os emails pendentes na fila?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, processar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mostrar indicador de carregamento
                            Swal.fire({
                                title: 'Processando fila de emails',
                                text: 'Por favor, aguarde...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Enviar requisição AJAX para processar fila
                            $.ajax({
                                url: 'process_email_queue.php',
                                type: 'POST',
                                success: function(response) {
                                    try {
                                        const data = JSON.parse(response);
                                        
                                        Swal.fire({
                                            title: 'Concluído',
                                            html: `Processamento concluído:<br>
                                                  <b>${data.processed}</b> emails processados<br>
                                                  <b>${data.success}</b> enviados com sucesso<br>
                                                  <b>${data.failed}</b> falhas`,
                                            icon: data.failed > 0 ? 'warning' : 'success',
                                            confirmButtonText: 'OK'
                                        });
                                    } catch (e) {
                                        Swal.fire({
                                            title: 'Erro',
                                            text: 'Erro ao processar a resposta do servidor',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Erro',
                                        text: 'Erro ao enviar requisição. Tente novamente.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        }
                    });
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>