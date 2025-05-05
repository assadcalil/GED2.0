<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * Serviço de Email de Notificação
 * Arquivo: services/EmailNotificacaoService.php
 */
require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/../templates/email_pagamento_notificacao.php';

class EmailNotificacaoService {
    
    private $mail;
    private $config;
    private $debug = true; // Habilitar para mais logs durante o diagnóstico
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->config = EmailConfig::getConfig();
        
        // Verificar se o PHPMailer está disponível
        if (!file_exists(__DIR__ . '/../phpmailer/class.phpmailer.php')) {
            throw new Exception('Biblioteca PHPMailer não encontrada');
        }
        
        // Incluir arquivos explicitamente
        require_once __DIR__ . '/../phpmailer/class.phpmailer.php';
        require_once __DIR__ . '/../phpmailer/class.smtp.php'; // Esta linha é crucial!
        
        // Não use PHPMailerAutoload.php se estiver incluindo os arquivos diretamente
        // require_once __DIR__ . '/../phpmailer/PHPMailerAutoload.php';
        
        $this->mail = new PHPMailer(true);
        $this->configurarSMTP();
    }
    
    /**
     * Configura as definições SMTP
     */
    private function configurarSMTP() {
        // Habilitar modo de debug para diagnóstico (temporariamente)
        $this->mail->SMTPDebug = $this->debug ? 2 : $this->config['smtp']['debug'];
        
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp']['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config['smtp']['user'];
        $this->mail->Password = $this->config['smtp']['pass'];
        $this->mail->SMTPSecure = $this->config['smtp']['secure'];
        $this->mail->Port = $this->config['smtp']['port'];
        $this->mail->CharSet = $this->config['geral']['charset'];
        $this->mail->IsHTML($this->config['geral']['is_html']);
        
        // Configurar remetente
        $this->mail->From = $this->config['remetente']['email'];
        $this->mail->FromName = $this->config['remetente']['nome'];
        
        // Verificar conexão para diagnóstico
        if ($this->debug) {
            $this->logDebug('Configuração SMTP: ' . json_encode([
                'host' => $this->config['smtp']['host'],
                'port' => $this->config['smtp']['port'],
                'user' => $this->config['smtp']['user'],
                'secure' => $this->config['smtp']['secure']
            ]));
        }
    }
    
    /**
     * Registra mensagem de debug
     * 
     * @param string $message Mensagem para registrar
     */
    private function logDebug($message) {
        if ($this->debug && class_exists('Logger')) {
            Logger::activity('email_debug', $message);
        }
        
        // Também registrar em arquivo para depuração
        ini_set('log_errors', 1);
        $logFile = __DIR__ . '/../logs/email_debug.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        file_put_contents(
            $logFile, 
            date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Envia email de notificação de pagamento com verificação de conectividade
     * 
     * @param array $cliente Dados do cliente (código, nome, CPF)
     * @param array $payment Dados do pagamento (data, valor, motivo)
     * @param array $user Dados do usuário destinatário
     * @return array Resultado do envio
     */
    public function enviarNotificacaoPagamento($cliente, $payment, $user) {
        // Verificar conectividade SMTP antes de tentar enviar
        try {
            // Teste de conectividade
            $smtp = fsockopen($this->config['smtp']['host'], $this->config['smtp']['port'], $errno, $errstr, 5);
            
            if (!$smtp) {
                $this->logDebug("Falha ao conectar ao servidor SMTP: $errstr ($errno)");
                return [
                    'sucesso' => false,
                    'mensagem' => "Não foi possível conectar ao servidor SMTP: $errstr ($errno)"
                ];
            }
            
            fclose($smtp);
            $this->logDebug("Conexão ao servidor SMTP estabelecida com sucesso");
            
        } catch (Exception $e) {
            $this->logDebug("Exceção ao verificar conectividade SMTP: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => "Erro ao verificar conectividade SMTP: " . $e->getMessage()
            ];
        }
        
        // Proceder com o envio do email
        try {
            // Limpa destinatários anteriores
            $this->mail->clearAddresses();
            
            // Adiciona destinatário
            $this->mail->addAddress($user['email'], $user['name']);
            $this->logDebug("Destinatário adicionado: {$user['email']} ({$user['name']})");
            
            // Define o assunto
            $assunto = "Notificação de Pagamento - Cliente {$cliente['codigo']}";
            $this->mail->Subject = $assunto;
            
            // Gera o corpo do email
            $mensagem = EmailTemplatePagamentoNotificacao::gerarHTML($cliente, $payment, $user);
            $this->mail->Body = $mensagem;
            $this->mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $mensagem));
            
            $this->logDebug("Email preparado para envio: assunto=$assunto, cliente={$cliente['codigo']}");
            
            // Tenta enviar o email com tentativas de retry
            $maxTentativas = 3;
            $tentativa = 0;
            $erro = null;
            
            while ($tentativa < $maxTentativas) {
                $tentativa++;
                $this->logDebug("Tentativa $tentativa de $maxTentativas");
                
                try {
                    if ($this->mail->send()) {
                        $this->logDebug("Email enviado com sucesso na tentativa $tentativa");
                        return [
                            'sucesso' => true,
                            'mensagem' => "Email enviado com sucesso na tentativa $tentativa",
                            'tentativas' => $tentativa
                        ];
                    }
                } catch (Exception $e) {
                    $erro = $e;
                    $this->logDebug("Erro na tentativa $tentativa: " . $e->getMessage());
                    
                    // Esperar antes da próxima tentativa
                    if ($tentativa < $maxTentativas) {
                        sleep(2);
                    }
                }
            }
            
            // Se chegou aqui, todas as tentativas falharam
            $mensagemErro = $erro ? $erro->getMessage() : $this->mail->ErrorInfo;
            $this->logDebug("Todas as $maxTentativas tentativas falharam. Último erro: $mensagemErro");
            
            return [
                'sucesso' => false,
                'mensagem' => "Falha ao enviar email após $maxTentativas tentativas: $mensagemErro",
                'tentativas' => $tentativa
            ];
            
        } catch (Exception $e) {
            $this->logDebug("Exceção ao enviar email: " . $e->getMessage());
            
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Implementa um envio alternativo usando a função mail() nativa do PHP
     * Útil quando o SMTP está com problemas
     * 
     * @param array $cliente Dados do cliente
     * @param array $payment Dados do pagamento
     * @param array $user Dados do usuário
     * @return array Resultado do envio
     */
    public function enviarNotificacaoPagamentoAlternativo($cliente, $payment, $user) {
        try {
            $this->logDebug("Tentando método alternativo de envio (mail nativo)");
            
            // Define o assunto
            $assunto = "Notificação de Pagamento - Cliente {$cliente['codigo']}";
            
            // Gera o corpo do email
            $mensagem = EmailTemplatePagamentoNotificacao::gerarHTML($cliente, $payment, $user);
            
            // Define os cabeçalhos
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: {$this->config['remetente']['nome']} <{$this->config['remetente']['email']}>" . "\r\n";
            
            // Tenta enviar usando a função mail() nativa
            if (mail($user['email'], $assunto, $mensagem, $headers)) {
                $this->logDebug("Email enviado com sucesso via método alternativo");
                return [
                    'sucesso' => true,
                    'mensagem' => "Email enviado com sucesso via método alternativo (mail nativo)",
                    'metodo' => 'nativo'
                ];
            } else {
                $erro = error_get_last()['message'] ?? 'Erro desconhecido';
                $this->logDebug("Falha no método alternativo: $erro");
                return [
                    'sucesso' => false,
                    'mensagem' => "Falha ao enviar email via método alternativo: $erro",
                    'metodo' => 'nativo'
                ];
            }
        } catch (Exception $e) {
            $this->logDebug("Exceção no método alternativo: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => "Exceção no método alternativo: " . $e->getMessage(),
                'metodo' => 'nativo'
            ];
        }
    }
    
    /**
     * Salva notificação em arquivo para envio posterior
     * Útil quando todos os métodos de envio falham
     * 
     * @param array $cliente Dados do cliente
     * @param array $payment Dados do pagamento
     * @param array $user Dados do usuário
     * @return array Resultado da operação
     */
    public function salvarNotificacaoEmFila($cliente, $payment, $user) {
        try {
            $this->logDebug("Salvando notificação em fila para envio posterior");
            
            // Diretório para a fila de emails
            $queueDir = __DIR__ . '/../queue/emails';
            if (!is_dir($queueDir)) {
                mkdir($queueDir, 0777, true);
            }
            
            // Criar identificador único para o arquivo
            $id = uniqid() . '_' . time();
            $filename = $queueDir . "/pagamento_{$cliente['codigo']}_{$id}.json";
            
            // Dados a serem salvos
            $dados = [
                'data_criacao' => date('Y-m-d H:i:s'),
                'tipo' => 'notificacao_pagamento',
                'tentativas' => 0,
                'cliente' => $cliente,
                'payment' => $payment,
                'user' => $user
            ];
            
            // Salvar em arquivo
            if (file_put_contents($filename, json_encode($dados, JSON_PRETTY_PRINT))) {
                $this->logDebug("Notificação salva em fila: $filename");
                return [
                    'sucesso' => true,
                    'mensagem' => "Notificação salva em fila para envio posterior",
                    'arquivo' => $filename
                ];
            } else {
                $this->logDebug("Falha ao salvar notificação em fila");
                return [
                    'sucesso' => false,
                    'mensagem' => "Falha ao salvar notificação em fila para envio posterior"
                ];
            }
        } catch (Exception $e) {
            $this->logDebug("Exceção ao salvar em fila: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => "Exceção ao salvar em fila: " . $e->getMessage()
            ];
        }
    }
}
?>