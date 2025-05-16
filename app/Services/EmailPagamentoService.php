<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * Serviço de Email para Notificações de Pagamento
 * Arquivo: app/Services/EmailPagamentoService.php
 */

// Definir diretório raiz para includes (se ainda não estiver definido)
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(dirname(dirname(__FILE__))));
}

// Incluir arquivos necessários (se não foram incluídos)
if (!file_exists(ROOT_DIR . '/phpmailer/PHPMailerAutoload.php')) {
    throw new Exception('Biblioteca PHPMailer não encontrada em: ' . ROOT_DIR . '/phpmailer/PHPMailerAutoload.php');
}

require_once ROOT_DIR . '/phpmailer/PHPMailerAutoload.php';
require_once ROOT_DIR . '/phpmailer/class.smtp.php';
require_once ROOT_DIR . '/templates/email_pagamento_notificacao.php';

/**
 * Classe para envio de emails de notificação de pagamento
 */
class EmailPagamentoService {
    // Propriedades da classe
    private $mail;
    private $smtpHost = 'smtp.gmail.com';
    private $smtpPort = 465;
    private $smtpUser = 'recuperacaoestrela@gmail.com';
    private $smtpPass = 'sgyrmsgdaxiqvupb';
    private $smtpSecure = 'ssl';
    private $emailRemetente = 'recuperacaoestrela@gmail.com';
    private $nomeRemetente = 'CONTABILIDADE ESTRELA';
    
    // Caminho para arquivo de log
    private $logFile;
    
    /**
     * Construtor
     */
    public function __construct() {
        // Configurar caminho para arquivo de log
        $logs_dir = ROOT_DIR . '/logs';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0777, true);
        }
        $this->logFile = $logs_dir . '/email_pagamento.log';
        
        // Inicializar PHPMailer
        try {
            $this->mail = new PHPMailer(true); // true para habilitar exceções
            $this->configurarSMTP();
        } catch (Exception $e) {
            $this->log('ERRO na inicialização: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Configurar as definições SMTP
     */
    private function configurarSMTP() {
        // Configurações básicas
        $this->mail->isSMTP();
        $this->mail->Host = $this->smtpHost;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->smtpUser;
        $this->mail->Password = $this->smtpPass;
        $this->mail->SMTPSecure = $this->smtpSecure;
        $this->mail->Port = $this->smtpPort;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->isHTML(true);
        
        // Configurar timeout maior
        $this->mail->Timeout = 60;
        
        // Configurar remetente
        $this->mail->setFrom($this->emailRemetente, $this->nomeRemetente);
        
        // Opções SSL para evitar erros de certificado
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $this->log('SMTP configurado: ' . $this->smtpHost . ':' . $this->smtpPort);
    }
    
    /**
     * Enviar email de notificação de pagamento
     * 
     * @param array $cliente Dados do cliente (codigo, nome, cpf)
     * @param array $payment Dados do pagamento (data_pagamento, valor, motivo)
     * @param array $user Dados do usuário (name, email)
     * @return array Resultado do envio (sucesso, mensagem)
     */
    public function enviarNotificacaoPagamento($cliente, $payment, $user) {
        try {
            $this->log('Iniciando envio para: ' . $user['name'] . ' <' . $user['email'] . '> | Cliente: ' . $cliente['codigo']);
            
            // Limpar destinatários anteriores
            $this->mail->clearAddresses();
            
            // Adicionar destinatário
            $this->mail->addAddress($user['email'], $user['name']);
            
            // Definir assunto
            $assunto = "Notificação de Pagamento - Cliente {$cliente['codigo']}";
            $this->mail->Subject = $assunto;
            
            // Gerar corpo do email
            $this->mail->Body = EmailTemplatePagamentoNotificacao::gerarHTML($cliente, $payment, $user);
            
            // Enviar o email
            if ($this->mail->send()) {
                $this->log('Email enviado com sucesso!');
                
                return [
                    'sucesso' => true,
                    'mensagem' => 'Email de notificação enviado com sucesso'
                ];
            } else {
                throw new Exception('Falha ao enviar email: ' . $this->mail->ErrorInfo);
            }
        } catch (Exception $e) {
            $this->log('ERRO: ' . $e->getMessage());
            
            // Tentar método alternativo
            $result = $this->enviarEmailAlternativo($cliente, $payment, $user);
            
            if ($result['sucesso']) {
                return $result;
            }
            
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Método alternativo de envio usando mail() nativo do PHP
     */
    private function enviarEmailAlternativo($cliente, $payment, $user) {
        try {
            $this->log('Tentando método alternativo via mail()');
            
            // Gerar o corpo do email
            $mensagem = EmailTemplatePagamentoNotificacao::gerarHTML($cliente, $payment, $user);
            
            // Define o assunto
            $assunto = "Notificação de Pagamento - Cliente {$cliente['codigo']}";
            
            // Define os cabeçalhos
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: {$this->nomeRemetente} <{$this->emailRemetente}>" . "\r\n";
            $headers .= "Reply-To: {$this->emailRemetente}" . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            
            // Tentativa de envio
            $emailSent = mail($user['email'], $assunto, $mensagem, $headers);
            
            if ($emailSent) {
                $this->log('Email enviado com sucesso via mail()!');
                
                return [
                    'sucesso' => true,
                    'mensagem' => 'Email enviado com sucesso via método alternativo'
                ];
            } else {
                $this->log('Falha ao enviar email via mail()');
                
                return [
                    'sucesso' => false,
                    'mensagem' => 'Falha ao enviar email via método alternativo'
                ];
            }
        } catch (Exception $e) {
            $this->log('ERRO no método alternativo: ' . $e->getMessage());
            
            return [
                'sucesso' => false,
                'mensagem' => 'Erro no método alternativo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar informações no arquivo de log
     */
    private function log($mensagem) {
        $entrada = date('Y-m-d H:i:s') . ' - ' . $mensagem . "\n";
        file_put_contents($this->logFile, $entrada, FILE_APPEND);
    }
}
?>