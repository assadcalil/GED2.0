<?php
    include_once 'lib/XmlDomConstruct.php';
    
    class RegistraBoleto{
        
        
        private $urlIntegracao = '';
        
        private $dadosXml;
        
        
        public function __construct($informacoes, $arrDescontos = null) {
            $this->_setConfigs($informacoes, $arrDescontos);
        }
        
        public function realizarRegistro() {

            try {
                $connCURL = curl_init($this->urlIntegracao);
                curl_setopt($connCURL, CURLOPT_POSTFIELDS, $this->dadosXml);
                curl_setopt($connCURL, CURLOPT_POST, true);
                curl_setopt($connCURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($connCURL, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($connCURL, CURLOPT_SSL_VERIFYHOST, false);

                curl_setopt($connCURL, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/xml',
                    'SOAPAction: "INCLUI_BOLETO"'
                ));

                $responseCURL = curl_exec($connCURL);
                $err = curl_error($connCURL);
                curl_close($connCURL);

                if ($err) {
                    return $infoArray;
                }

                $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $responseCURL);
                $xml = new SimpleXMLElement($response);
                $xmlArray = json_decode(json_encode((array) $xml), TRUE);
                $infoArray = $xmlArray['soapenvBody']['manutencaocobrancabancariaSERVICO_SAIDA']['DADOS'];
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            if (isset($infoArray['EXCECAO'])) {
                return $infoArray;
            }

            if ($infoArray['CONTROLE_NEGOCIAL']['COD_RETORNO'] == '00') {
                return $infoArray;
            } else {
                return $infoArray;
            }
        }
        
        
        private function _setConfigs($informacoes, $arrDescontos = null) {
        $this->urlIntegracao = $informacoes['urlIntegracao'];

        if ($arrDescontos == null) {
            $arrDescontos = array();
        }

        if (count($arrDescontos) > 0) {
            foreach ($arrDescontos as $desconto) {
                $descontosCaixa[] = array(
                    'DESCONTO' => array(
                        'DATA' => $desconto['dataValidade'],
                        'VALOR' => $desconto['valor']
                    )
                );
            }
        } else {
            $descontosCaixa = array();
        }

        $arrayDadosHash = array(
            'codigoCedente' => $informacoes['codigoCedente'],
            'nossoNumero' => $informacoes['nossoNumero'],
            'dataVencimento' => $informacoes['dataVencimento'],
            'valorNominal' => $informacoes['valorNominal'],
            'cnpj' => $informacoes['cnpj']
        );

        $autenticacao = $this->_geraHashAutenticacao($arrayDadosHash);

            $arrayDados = array(
                'soapenv:Body' => array(
                    'ext:SERVICO_ENTRADA' => array(
                        'sib:HEADER' => array(
                            'VERSAO' => '3.2',
                            'AUTENTICACAO' => $autenticacao,
                            'USUARIO_SERVICO' => 'SGCBS02P', //SGCBS02P - Produção | SGCBS01D - Desenvolvimento
                            'OPERACAO' => 'INCLUI_BOLETO', //Implementado apenas para inclusão
                            'SISTEMA_ORIGEM' => 'SIGCB',
                            'UNIDADE' => $informacoes['numeroAgencia'],
                            'DATA_HORA' => date('YmdHis')
                        ),
                        'DADOS' => array(
                            'INCLUI_BOLETO' => array(
                                'CODIGO_BENEFICIARIO' => $informacoes['codigoCedente'],
                                'TITULO' => array(
                                    'NOSSO_NUMERO' => $informacoes['nossoNumero'],
                                    'NUMERO_DOCUMENTO' => $informacoes['codigoTitulo'], //código interdo do boleto/título
                                    'DATA_VENCIMENTO' => $informacoes['dataVencimento'],
                                    'VALOR' => $informacoes['valorNominal'],
                                    'TIPO_ESPECIE' => '99', // Olhar no manual qual enviar
                                    'FLAG_ACEITE' => 'N', // S-Aceite | N-Não aceite (reconhecimento de dívida pelo pagador)
                                    'DATA_EMISSAO' => $informacoes['dataEmissao'],
                                    'JUROS_MORA' => array(
                                        'TIPO' => 'TAXA_MENSAL',
                                        'DATA' => $informacoes['dataJuros'],
                                        'PERCENTUAL' => $informacoes['juros'],
                                    ),
                                    'VALOR_ABATIMENTO' => '0',
                                    'POS_VENCIMENTO' => array(
                                        'ACAO' => 'DEVOLVER',
                                        'NUMERO_DIAS' => '5',
                                    ),
                                    'CODIGO_MOEDA' => '09', //Real
                                    'PAGADOR' => $informacoes['infoPagador'],
                                    'MULTA' => array(
                                        'DATA' => $informacoes['dataMulta'],
                                        'PERCENTUAL' => $informacoes['multa'],
                                    ),
                                    'DESCONTOS' => $descontosCaixa
                                )
                            )
                        )
                    )
                )
            );
            $this->_geraEstruturaXml($arrayDados);
        }
        
        private function _geraHashAutenticacao(array $arrayDadosHash) {
            $numeroParaHash = preg_replace('/[^A-Za-z0-9]/', '', str_pad($arrayDadosHash['codigoCedente'], 7, '0', STR_PAD_LEFT) .
                            $arrayDadosHash['nossoNumero'] .
                            strftime('%d%m%Y', strtotime($arrayDadosHash['dataVencimento']))) .
                    str_pad(preg_replace('/[^0-9]/', '', $arrayDadosHash['valorNominal']), 15, '0', STR_PAD_LEFT) .
                    str_pad($arrayDadosHash['cnpj'], 14, '0', STR_PAD_LEFT);

            $autenticacao = base64_encode(hash('sha256', $numeroParaHash, true));
            return $autenticacao;
        }
        
        private function _geraEstruturaXml(array $arrayDados) {
            $xml_root = 'soapenv:Envelope';
            $xml = new XmlDomConstruct('1.0', 'utf-8');
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput = true;
            $xml->convertArrayToXml(array($xml_root => $arrayDados));
            $xml_root_item = $xml->getElementsByTagName($xml_root)->item(0);
            $xml_root_item->setAttribute(
                    'xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/'
            );
            $xml_root_item->setAttribute(
                    'xmlns:ext', 'http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo'
            );
            $xml_root_item->setAttribute(
                    'xmlns:sib', 'http://caixa.gov.br/sibar'
            );
            file_put_contents('xmldadosthi.xml', $xml->saveXML());

            $this->dadosXml = $xml->saveXML();
        }
        
    }
