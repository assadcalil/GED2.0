<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * Serviço de Email para Usuários - Com Notificação de Pagamento
 * Arquivo: services/EmailUsuarioService.php
 */
require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/../templates/email_novo_usuario.php';

class EmailUsuarioService {
    
    private $mail;
    private $config;
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->config = EmailConfig::getConfig();
        
        // Verifica se o PHPMailer está disponível
        if (!file_exists(__DIR__ . '/../phpmailer/PHPMailerAutoload.php')) {
            throw new Exception('Biblioteca PHPMailer não encontrada');
        }
        
        require_once __DIR__ . '/../phpmailer/PHPMailerAutoload.php';
        require_once __DIR__ . '/../phpmailer/class.smtp.php';
        
        $this->mail = new PHPMailer(true);
        $this->configurarSMTP();
    }
    
    /**
     * Configura as definições SMTP
     */
    private function configurarSMTP() {
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp']['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config['smtp']['user'];
        $this->mail->Password = $this->config['smtp']['pass'];
        $this->mail->SMTPSecure = $this->config['smtp']['secure'];
        $this->mail->Port = $this->config['smtp']['port'];
        $this->mail->SMTPDebug = $this->config['smtp']['debug'];
        $this->mail->CharSet = $this->config['geral']['charset'];
        $this->mail->IsHTML($this->config['geral']['is_html']);
        
        // Configurar remetente
        $this->mail->From = $this->config['remetente']['email'];
        $this->mail->FromName = $this->config['remetente']['nome'];
    }
    
    /**
     * Envia email de boas-vindas para novo usuário
     * 
     * @param array $userData Dados do usuário
     * @param string $password Senha gerada para o usuário
     * @return array Resultado do envio
     */
    public function enviarEmailBoasVindas($userData, $password) {
        try {
            // Limpa destinatários anteriores
            $this->mail->clearAddresses();
            
            // Adiciona destinatário
            $this->mail->addAddress($userData['email']);
            
            // Adiciona cópia para administração se configurado
            if (isset($this->config['destinatarios']['copia']) && !empty($this->config['destinatarios']['copia'])) {
                $this->mail->addBCC($this->config['destinatarios']['copia']);
            }
            
            // Define o assunto
            $assunto = "Bem-vindo à Contabilidade Estrela - Suas Credenciais de Acesso";
            $this->mail->Subject = $assunto;
            
            // Gera o corpo do email
            $this->mail->Body = EmailTemplateNovoUsuario::gerarHTML($userData, $password);
            
            // Envia o email
            if ($this->mail->send()) {
                return [
                    'sucesso' => true,
                    'mensagem' => 'Email enviado com sucesso'
                ];
            } else {
                throw new Exception('Falha ao enviar email: ' . $this->mail->ErrorInfo);
            }
            
        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

}
?>