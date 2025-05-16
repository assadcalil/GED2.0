<?php
/**
 * Sistema Contabilidade Estrela 2.0
 * Template de Email para Nova Empresa
 * Arquivo: templates/email_nova_empresa.php
 */

class EmailTemplateNovaEmpresa {
    /**
     * Gera o HTML do email para nova empresa cadastrada
     * 
     * @param array $empresaData Dados da empresa cadastrada
     * @return string HTML formatado do email
     */
    public static function gerarHTML($empresaData) {
        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Nova Empresa - ' . htmlspecialchars($empresaData['emp_name']) . '</title>
        </head>
        <body style="font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; color: #333333;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border: 1px solid #dddddd; border-radius: 5px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 2px solid #0078D4;">
                    <h1 style="color: #0078D4; margin: 0;">Nova Empresa Cadastrada</h1>
                    <p style="color: #666666;">Contabilidade Estrela</p>
                </div>
                
                <div style="padding: 20px 0;">
                    <p>Prezado(a),</p>
                    
                    <p>Uma nova empresa foi cadastrada no sistema Contabilidade Estrela 2.0 com os seguintes dados:</p>
                    
                    <div style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; margin: 20px 0;">
                        <h2 style="color: #0078D4; margin-top: 0; font-size: 18px;">Informações da Empresa</h2>
                        
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold; width: 40%;">Código:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_code']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Razão Social:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_name']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">CNPJ:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_cnpj']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Situação:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_sit_cad']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Tipo Jurídico:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_tipo_jur']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Telefone:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_tel']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">E-mail:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['email_empresa']) . '</td>
                            </tr>
                        </table>
                        
                        <h3 style="color: #0078D4; margin-top: 20px; font-size: 16px;">Sócio Principal</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold; width: 40%;">Nome:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['soc1_name']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">CPF:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['soc1_cpf']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">E-mail:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['soc1_email']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Telefone:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['soc1_tel']) . '</td>
                            </tr>
                        </table>
                        
                        <h3 style="color: #0078D4; margin-top: 20px; font-size: 16px;">Endereço</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold; width: 40%;">CEP:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_cep']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Endereço:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_ende']) . ', ' . htmlspecialchars($empresaData['emp_nume']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Complemento:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_comp']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Bairro:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_bair']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 10px; font-weight: bold;">Cidade/UF:</td>
                                <td style="padding: 5px 10px;">' . htmlspecialchars($empresaData['emp_cid']) . '/' . htmlspecialchars($empresaData['emp_uf']) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Este e-mail é gerado automaticamente pelo sistema. Em caso de dúvidas ou informações adicionais, entre em contato conosco.</p>
                    
                    <p style="margin-top: 20px; text-align: center;">
                        <a href="https://contabilidadeestrela.com.br/ged2.0/" style="display: inline-block; background-color: #0078D4; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold;">Acessar Sistema</a>
                    </p>
                    
                    <p>Atenciosamente,<br>
                    Equipe Contabilidade Estrela<br>
                    Tel: (11) 2124-7070<br>
                    Email: cestrela@terra.com.br</p>
                </div>
                
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #777777; font-size: 12px;">
                    &copy; ' . date('Y') . ' Contabilidade Estrela - Todos os direitos reservados
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Gera o assunto do email para nova empresa
     * 
     * @param array $empresaData Dados da empresa
     * @return string Assunto formatado
     */
    public static function gerarAssunto($empresaData) {
        return 'Nova Empresa Cadastrada - ' . $empresaData['emp_code'] . ' - ' . $empresaData['emp_name'];
    }
}