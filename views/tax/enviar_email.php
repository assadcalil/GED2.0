<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * Função para enviar emails de notificação de pagamento para o endereço fixo cestrela.cancelar@terra.com.br
 */

// Incluir a biblioteca PHPMailer
require_once ROOT_DIR . '/phpmailer/Exception.php';
require_once ROOT_DIR . '/phpmailer/PHPMailer.php';
require_once ROOT_DIR . '/phpmailer/SMTP.php';

require_once ROOT_DIR . '/app/Config/Email.php';

/**
 * Envia email de notificação de pagamento para o endereço fixo cestrela.cancelar@terra.com.br
 * 
 * @param array $cliente Dados do cliente (codigo, nome, cpf)
 * @param array $paymentInfo Dados do pagamento (data_pagamento, valor, motivo)
 * @return bool Retorna true se o email foi enviado com sucesso, false caso contrário
 */
function enviarEmailNotificacao($cliente, $paymentInfo) {
    try {
        // Inicializar PHPMailer
        $mail = new PHPMailer(true);
        
        // Obter configurações de email
        $config = EmailConfig::getConfig();
        
        // Configurar servidor SMTP
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['user'];
        $mail->Password   = $config['smtp']['pass'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        $mail->CharSet    = $config['geral']['charset'];
        
        // Habilitar debug se necessário
        $mail->SMTPDebug  = $config['smtp']['debug'];
        
        // Configurar remetente
        $mail->setFrom($config['remetente']['email'], $config['remetente']['nome']);
        
        // Configurar destinatário - APENAS para o email específico
        $mail->addAddress(EmailConfig::EMAIL_COPIA);
        
        // Configurar assunto
        $assunto = "Pagamento de Imposto de Renda - Cliente #{$cliente['codigo']} - {$cliente['nome']}";
        $mail->Subject = $assunto;
        
        // Formatar valor para exibição
        $valor_formatado = 'R$ ' . number_format((float)$paymentInfo['valor'], 2, ',', '.');
        
        // Criar corpo do email
        $corpo = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                .info-label { font-weight: bold; }
                .highlight { background-color: #f8f9fa; padding: 10px; border-left: 3px solid #4CAF50; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Notificação de Pagamento - Imposto de Renda</h2>
                </div>
                <div class='content'>
                    <p>Um pagamento de Imposto de Renda foi registrado no sistema com as seguintes informações:</p>
                    
                    <h3>Dados do Cliente</h3>
                    <p><span class='info-label'>Código:</span> {$cliente['codigo']}</p>
                    <p><span class='info-label'>Nome:</span> {$cliente['nome']}</p>
                    <p><span class='info-label'>CPF:</span> {$cliente['cpf']}</p>
                    
                    <h3>Dados do Pagamento</h3>
                    <div class='highlight'>
                        <p><span class='info-label'>Data de Pagamento:</span> {$paymentInfo['data_pagamento']}</p>
                        <p><span class='info-label'>Valor Pago:</span> {$valor_formatado}</p>
                        <p><span class='info-label'>Forma de Pagamento:</span> {$paymentInfo['motivo']}</p>
                    </div>
                    
                    <p>Este email é uma notificação automática. O sistema já atualizou o status do boleto para 'Pago'.</p>
                </div>
                <div class='footer'>
                    <p>Esta é uma mensagem automática do Sistema de Contabilidade Estrela. Por favor, não responda a este email.</p>
                    <p>© " . date('Y') . " Contabilidade Estrela - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Configurar corpo do email
        $mail->isHTML(true);
        $mail->Body = $corpo;
        $mail->AltBody = strip_tags(str_replace(['<div>', '</div>', '<p>', '</p>'], ["\n", "", "\n", ""], $corpo));
        
        // Enviar email
        $result = $mail->send();
        
        // Registrar no log de atividades
        if (class_exists('Logger')) {
            Logger::activity(
                'email', 
                "Notificação de pagamento enviada: Cliente #{$cliente['codigo']} - {$cliente['nome']} - {$valor_formatado}" .
                " - Email enviado para: " . EmailConfig::EMAIL_COPIA
            );
        }
        
        return $result;
    } catch (Exception $e) {
        // Registrar erro no log
        if (class_exists('Logger')) {
            Logger::activity(
                'erro', 
                "Erro ao enviar email de notificação para Cliente #{$cliente['codigo']}: " . $e->getMessage()
            );
        }
        
        return false;
    }
}