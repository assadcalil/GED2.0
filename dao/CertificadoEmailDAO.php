<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * DAO para gerenciamento de emails de certificados digitais
 */

// Importações do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verificar se as configurações já foram incluídas
if (!defined('ROOT_DIR')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/log.php';
}

// Incluir as classes do PHPMailer
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

/**
 * Classe responsável por gerenciar o envio de emails relacionados a certificados digitais
 */
class CertificadoEmailDAO {
    private $conn;
    
    /**
     * Construtor - inicializa a conexão com o banco de dados
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    /**
     * Envia email de notificação de cadastro de certificado digital
     * 
     * @param array $dados Dados do certificado para o email
     * @return array Resultado da operação com status e mensagem
     */
    public function enviarEmailNotificacao($dados) {
        try {
            // Verificar se recebeu todos os dados necessários
            if (!isset($dados['certificado_id']) || 
                !isset($dados['emp_name']) || 
                !isset($dados['emp_cnpj']) || 
                !isset($dados['tipo_certificado']) || 
                !isset($dados['certificado_emissao']) || 
                !isset($dados['certificado_validade']) || 
                !isset($dados['certificado_situacao'])) {
                    
                Logger::warning("Tentativa de envio de notificação com dados incompletos", $dados);
                return array('sucesso' => false, 'mensagem' => 'Dados incompletos para envio');
            }
            
            // Instância do objeto PHPMailer
            $mail = new PHPMailer(true); // Habilita exceções
            $mail->CharSet = 'UTF-8';
            
            // Configurar para envio de e-mails usando SMTP
            $mail->isSMTP();
            
            // Configurações do SMTP
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'recuperacaoestrela@gmail.com';
            $mail->Password   = 'sgyrmsgdaxiqvupb';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Configurações do email
            $mail->setFrom('recuperacaoestrela@gmail.com', 'CONTABILIDADE ESTRELA');
            
            // Destinatário principal
            $mail->addAddress('cestrela.cancelar@terra.com.br');
            
            // Assunto do email
            $assunto = 'Novo Certificado Digital Cadastrado - ' . $dados['emp_name'];
            $mail->Subject = $assunto;
            
            // Corpo do email em HTML
            $mail->isHTML(true);
            $mail->Body = $this->gerarCorpoEmail($dados);
            
            // Enviar email
            try {
                $mail->send();
                
                // Registrar log do envio
                Logger::activity('email', "Email de notificação enviado para cestrela.cancelar@terra.com.br sobre cadastro de certificado ID: {$dados['certificado_id']}");
                
                return array(
                    'sucesso' => true, 
                    'mensagem' => 'Email enviado com sucesso!'
                );
                
            } catch (Exception $e) {
                // Registrar log do erro
                Logger::error('email', "Falha ao enviar email de notificação: " . $e->getMessage(), [
                    'certificado_id' => $dados['certificado_id'],
                    'emp_name' => $dados['emp_name']
                ]);
                
                return array(
                    'sucesso' => false, 
                    'mensagem' => 'Erro ao enviar email: ' . $e->getMessage()
                );
            }
            
        } catch (Exception $e) {
            // Registrar erro no log
            Logger::error("Erro no processo de envio de notificação: " . $e->getMessage(), $dados ?? []);
            
            return array(
                'sucesso' => false, 
                'mensagem' => 'Erro ao enviar email: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Gera o corpo HTML do email de notificação
     * 
     * @param array $dados Dados do certificado
     * @return string HTML formatado para o email
     */
    private function gerarCorpoEmail($dados) {
        // Extrair os dados para uso no template
        extract($dados);
        
        // Preparar situação do certificado para exibição
        $situacaoTexto = '';
        switch ($certificado_situacao) {
            case 'VIGENTE':
                $situacaoTexto = 'Vigente';
                break;
            case 'VENCIDO':
                $situacaoTexto = 'Vencido';
                break;
            case 'PRESTES_A_VENCER':
                $situacaoTexto = 'Prestes a Vencer';
                break;
            case 'RENOVACAO_PENDENTE':
                $situacaoTexto = 'Renovação Pendente';
                break;
            default:
                $situacaoTexto = $certificado_situacao;
        }
        
        // Iniciar buffer de saída
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Novo Certificado Digital Cadastrado</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 650px;
            margin: 0 auto;
        }
        .header {
            background-color: #0078D4;
            color: white;
            padding: 25px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            padding: 25px;
            background-color: #f9f9f9;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }
        .section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            background-color: #f0f7ff;
            padding: 12px;
            border-radius: 5px;
            border-left: 3px solid #0078D4;
        }
        .info-item.date {
            border-left: 3px solid #e81123;
        }
        .info-label {
            font-weight: bold;
            color: #0078D4;
            font-size: 14px;
            margin-bottom: 3px;
        }
        .info-label.date {
            color: #e81123;
        }
        .info-value {
            color: #333;
            font-size: 15px;
        }
        .info-value.date {
            color: #e81123;
            font-weight: bold;
        }
        .footer {
            background-color: #f2f2f2;
            padding: 15px 25px;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #555;
            border-top: 3px solid #0078D4;
        }
        .signature {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 14px;
        }
        .vencimento-container {
            text-align: center;
            margin: 20px 0;
        }
        .vencimento-label {
            font-weight: bold;
            color: #e81123;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .vencimento-value {
            color: #e81123;
            font-weight: bold;
            font-size: 22px;
        }
        h2 {
            color: #0078D4;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #0078D4;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Novo Certificado Digital Cadastrado</h1>
    </div>
    
    <div class="content">
        <p>Prezada Equipe da Contabilidade Estrela,</p>
        
        <p>Este é um email automático para informar que um novo certificado digital foi cadastrado no sistema.</p>
        
        <div class="section">
            <h2><center>Informações do Certificado</center></h2>
            
            <table>
                <tr>
                    <th>ID do Certificado</th>
                    <td><?php echo htmlspecialchars($certificado_id); ?></td>
                </tr>
                <tr>
                    <th>Empresa</th>
                    <td><?php echo htmlspecialchars($emp_name); ?></td>
                </tr>
                <tr>
                    <th>CNPJ</th>
                    <td><?php echo htmlspecialchars($emp_cnpj); ?></td>
                </tr>
                <tr>
                    <th>Tipo do Certificado</th>
                    <td><?php echo htmlspecialchars($tipo_certificado); ?></td>
                </tr>
                <tr>
                    <th>Data de Emissão</th>
                    <td><?php echo htmlspecialchars($certificado_emissao); ?></td>
                </tr>
                <tr>
                    <th>Data de Validade</th>
                    <td><?php echo htmlspecialchars($certificado_validade); ?></td>
                </tr>
                <tr>
                    <th>Situação</th>
                    <td><?php echo htmlspecialchars($situacaoTexto); ?></td>
                </tr>
                <?php if (!empty($certificado_responsavel)): ?>
                <tr>
                    <th>Responsável</th>
                    <td><?php echo htmlspecialchars($certificado_responsavel); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Cadastrado por</th>
                    <td><?php echo htmlspecialchars($usuario); ?></td>
                </tr>
                <tr>
                    <th>Data do Cadastro</th>
                    <td><?php echo htmlspecialchars($data_cadastro); ?></td>
                </tr>
            </table>
            
            <!-- Data de vencimento destacada no centro -->
            <div class="vencimento-container">
                <div class="vencimento-label">DATA DE VENCIMENTO</div>
                <div class="vencimento-value"><?php echo htmlspecialchars($certificado_validade); ?></div>
            </div>
        </div>
        
        <p>Para visualizar mais detalhes ou fazer alterações, acesse o <a href="<?php echo htmlspecialchars($url_sistema); ?>">Sistema Contabilidade Estrela</a>.</p>
        
        <div class="signature">
            <strong>CONTABILIDADE ESTRELA</strong><br>
            Setor de Certificados Digitais<br>
            (11) 2124-7070<br>
            cestrela.cancelar@terra.com.br
        </div>
    </div>
    
    <div class="footer">
        <p><center>Este email contém informações confidenciais. Por favor, trate com a devida segurança.</center></p>
        <p><center>© <?php echo date('Y'); ?> Contabilidade Estrela - Todos os direitos reservados.</center></p>
    </div>
</body>
</html>
        <?php
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
}
?>