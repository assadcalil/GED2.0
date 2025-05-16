<?php
// Arquivo: templates/emails/payment_notification.php

/**
 * Template de notificação de pagamento
 * 
 * Variáveis disponíveis:
 * $cliente - array com informações do cliente (codigo, nome, cpf)
 * $payment - array com informações do pagamento (data_pagamento, valor, motivo)
 * $user - array com informações do usuário (name, email)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4a7eb5 0%, #2b5ea2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .footer {
            background: #f4f4f4;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f9f9f9;
            border-left: 4px solid #4a7eb5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .highlight {
            color: #4a7eb5;
            font-weight: bold;
        }
        .logo {
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #4a7eb5;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .divider {
            border-top: 1px solid #eee;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <!-- Logo pode ser colocado aqui -->
                <h1 style="margin:0;">Contabilidade Estrela</h1>
            </div>
            <h2 style="margin:0;font-size:24px;">Notificação de Pagamento</h2>
        </div>
        
        <div class="content">
            <p>Olá, <strong><?php echo htmlspecialchars($user['name']); ?></strong>!</p>
            
            <p>Gostaríamos de informar que identificamos um pagamento para o cliente:</p>
            
            <div class="info-box">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($cliente['codigo']); ?> - <?php echo htmlspecialchars($cliente['nome']); ?></p>
                <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></p>
                <p><strong>Data do Pagamento:</strong> <?php echo htmlspecialchars($payment['data_pagamento']); ?></p>
                <p><strong>Valor Pago:</strong> <span class="highlight">R$ <?php echo number_format((float)$payment['valor'], 2, ',', '.'); ?></span></p>
                <p><strong>Forma de Pagamento:</strong> <?php echo htmlspecialchars($payment['motivo']); ?></p>
            </div>
            
            <p>O status do boleto já foi atualizado automaticamente em nosso sistema para <strong>"Pago"</strong>.</p>
            
            <div class="divider"></div>
            
            <p>Para mais detalhes, acesse o sistema:</p>
            <a href="https://sistema.contabilidadeestrela.com.br" class="btn">Acessar Sistema</a>
            
            <div class="divider"></div>
            
            <p>Atenciosamente,<br><strong>Equipe Contabilidade Estrela</strong></p>
        </div>
        
        <div class="footer">
            <p>Esta é uma mensagem automática, por favor não responda este email.</p>
            <p>&copy; <?php echo date('Y'); ?> Contabilidade Estrela - Todos os direitos reservados</p>
        </div>
    </div>
</body>
</html>