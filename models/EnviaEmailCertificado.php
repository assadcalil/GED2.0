<?php
/**
 * Modelo para representar um Certificado Digital
 * Contém as propriedades e métodos de validação
 */
class EnviaEmailCertificado {
    // Propriedades da empresa
    private $emp_code;
    private $emp_name;
    private $emp_cnpj;
    
    // Propriedades do certificado
    private $tipo_certificado;
    private $data_renovacao;
    private $certificado_vencimento;
    private $arquivo_certificado;
    private $senha_certificado;
    
    // Propriedades de contato
    private $emails_destinatario;
    
    // Propriedades do remetente (fixas)
    private $nome_remetente = 'Contabilidade Estrela';
    private $cargo_remetente = 'Setor Certificado Digital';
    private $empresa_remetente = 'Contabilidade Estrela';
    private $telefone_remetente = '(11) 2124-7070';
    private $email_remetente = 'certificados@contabilidadeestrela.com.br';
    private $email_copia = 'cestrela.cancelar@terra.com.br';
    
    /**
     * Construtor
     */
    public function __construct($dados = array()) {
        if (!empty($dados)) {
            $this->setDados($dados);
        }
    }
    
    /**
     * Define os dados do modelo a partir de um array
     */
    public function setDados($dados) {
        // Dados da empresa
        if (isset($dados['emp_code'])) $this->emp_code = $dados['emp_code'];
        if (isset($dados['emp_name'])) $this->emp_name = $dados['emp_name'];
        if (isset($dados['emp_cnpj'])) $this->emp_cnpj = $dados['emp_cnpj'];
        
        // Dados do certificado
        if (isset($dados['tipo_certificado'])) $this->tipo_certificado = $dados['tipo_certificado'];
        if (isset($dados['data_renovacao'])) $this->data_renovacao = $dados['data_renovacao'];
        if (isset($dados['certificado_vencimento'])) $this->certificado_vencimento = $dados['certificado_vencimento'];
        if (isset($dados['arquivo_certificado'])) $this->arquivo_certificado = $dados['arquivo_certificado'];
        if (isset($dados['senha_certificado'])) $this->senha_certificado = $dados['senha_certificado'];
        
        // Dados de contato
        if (isset($dados['emails_destinatario'])) $this->emails_destinatario = $dados['emails_destinatario'];
    }
    
    /**
     * Retorna os dados do modelo como um array
     */
    public function getDados() {
        return array(
            // Dados da empresa
            'emp_code' => $this->emp_code,
            'emp_name' => $this->emp_name,
            'emp_cnpj' => $this->emp_cnpj,
            
            // Dados do certificado
            'tipo_certificado' => $this->tipo_certificado,
            'data_renovacao' => $this->data_renovacao,
            'certificado_vencimento' => $this->certificado_vencimento,
            'arquivo_certificado' => $this->arquivo_certificado,
            'senha_certificado' => $this->senha_certificado,
            
            // Dados de contato
            'emails_destinatario' => $this->emails_destinatario,
            
            // Dados do remetente
            'nome_remetente' => $this->nome_remetente,
            'cargo_remetente' => $this->cargo_remetente,
            'empresa_remetente' => $this->empresa_remetente,
            'telefone_remetente' => $this->telefone_remetente,
            'email_remetente' => $this->email_remetente,
            'email_copia' => $this->email_copia
        );
    }
    
    /**
     * Getter específico para o código da empresa (compatibilidade)
     */
    public function getEmp_code() {
        return $this->emp_code;
    }
    
    /**
     * Setter específico para o código da empresa (compatibilidade)
     */
    public function setEmp_code($emp_code) {
        $this->emp_code = $emp_code;
    }
    
    /**
     * Getter para senha do certificado
     */
    public function getSenhaCertificado() {
        return $this->senha_certificado;
    }
    
    /**
     * Setter para senha do certificado
     */
    public function setSenhaCertificado($senha_certificado) {
        $this->senha_certificado = $senha_certificado;
    }
    
    /**
     * Valida os dados do certificado
     */
    public function validar() {
        $erros = array();
        
        // Validar dados da empresa
        if (empty($this->emp_code)) {
            $erros[] = 'O número da empresa é obrigatório';
        }
        
        if (empty($this->emp_name)) {
            $erros[] = 'A razão social da empresa é obrigatória';
        }
        
        if (empty($this->emp_cnpj)) {
            $erros[] = 'O CNPJ da empresa é obrigatório';
        }
        
        // Validar dados do certificado
        if (empty($this->tipo_certificado)) {
            $erros[] = 'O tipo de certificado é obrigatório';
        }
        
        if (empty($this->data_renovacao)) {
            $erros[] = 'A data de renovação é obrigatória';
        }
        
        if (empty($this->certificado_vencimento)) {
            $erros[] = 'A data de vencimento é obrigatória';
        }
        
        // Validar datas
        if (!empty($this->data_renovacao) && !empty($this->certificado_vencimento)) {
            $dataRenovacao = new DateTime($this->data_renovacao);
            $dataVencimento = new DateTime($this->certificado_vencimento);
            
            if ($dataVencimento < $dataRenovacao) {
                $erros[] = 'A data de vencimento não pode ser anterior à data de renovação';
            }
        }
        
        // Validar arquivo
        if (empty($this->arquivo_certificado)) {
            $erros[] = 'O arquivo do certificado é obrigatório';
        } elseif (isset($this->arquivo_certificado['error']) && $this->arquivo_certificado['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Erro ao carregar o arquivo do certificado';
        }
        
        // Validar emails
        if (empty($this->emails_destinatario)) {
            $erros[] = 'Pelo menos um email de destinatário é obrigatório';
        } else {
            $emails = explode(',', $this->emails_destinatario);
            $invalidEmails = array();
            
            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = $email;
                }
            }
            
            if (!empty($invalidEmails)) {
                $erros[] = 'Os seguintes emails possuem formato inválido: ' . implode(', ', $invalidEmails);
            }
        }
        
        return $erros;
    }
    
    /**
     * Valida apenas os dados necessários para a prévia
     */
    public function validarPrevia() {
        $erros = array();
        
        // Validar dados da empresa
        if (empty($this->emp_code)) {
            $erros[] = 'O número da empresa é obrigatório';
        }
        
        if (empty($this->emp_name)) {
            $erros[] = 'A razão social da empresa é obrigatória';
        }
        
        // Validar dados do certificado
        if (empty($this->tipo_certificado)) {
            $erros[] = 'O tipo de certificado é obrigatório';
        }
        
        if (empty($this->data_renovacao)) {
            $erros[] = 'A data de renovação é obrigatória';
        }
        
        if (empty($this->certificado_vencimento)) {
            $erros[] = 'A data de vencimento é obrigatória';
        }
        
        // Validar datas
        if (!empty($this->data_renovacao) && !empty($this->certificado_vencimento)) {
            $dataRenovacao = new DateTime($this->data_renovacao);
            $dataVencimento = new DateTime($this->certificado_vencimento);
            
            if ($dataVencimento < $dataRenovacao) {
                $erros[] = 'A data de vencimento não pode ser anterior à data de renovação';
            }
        }
        
        return $erros;
    }
}
?>