<?php
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
    <HTML>
        <head>
            <TITLE><?php echo $dadosboleto["identificacao"]; ?></TITLE>
            <!--<META http-equiv=Content-Type content=text/html charset=ISO-8859-1>-->
            <META http-equiv=Content-Type content="text/html; charset=UTF-8">
            <meta name="Generator" content="Projeto BoletoPHP - www.boletophp.com.br - Licen�a GPL" />
                <style type=text/css>
                    <!--.cp {  font: bold 10px Arial; color: black}
                    <!--.ti {  font: 9px Arial, Helvetica, sans-serif}
                    <!--.ld {  font: bold 15px Arial; color: #000000}
                    <!--.ct {  font: 9px "Arial Narrow"; COLOR: #000033} 
                    <!--.cn {  font: 9px Arial; COLOR: black }
                    <!--.bc {  font: bold 20px Arial; color: #000000 }
                    <!--.ld2 { font: bold 12px Arial; color: #000000 }
                </style> 
        </head>
        <body>
        <div class="container-fluid py-0" style="margin-top: -40px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-15 mx-auto">
                            <!-- form card login -->
                            <div class="card rounded-0 shadow p-3 mb-5">
                                <div class="card-body">
                                <BODY text=#000000 bgColor=#ffffff topMargin=0 rightMargin=0>
                                    <table width=666 cellspacing=0 cellpadding=0 border=0>
                                        <!--<tr>
                                            <td valign=top class=cp>
                                                <DIV ALIGN="CENTER">Instruções de Impressão
                                                </DIV>
                                            </td>
                                        </tr>-->
                                        <!--<tr>
                                            <td valign=top class=cp>
                                                <div ALIGN="left">
                                                    <p>
                                                        <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo económico).<br>
                                                        <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas á esquerda e á direita do formulário.<br>
                                                        <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.<br>
                                                        <li>Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.
                                                        <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrónico ou no internet banking:<br>
                                                        <br>-->
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <center><img src="/GED2.0/assets/img/boleto/estrela.png" width="20%"></center></font><br>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <TBODY>
                                            <TR>
                                                <TD class=ct width=666><img height=1 src="/GED2.0/assets/img/boleto/6.png" width=665 border=0></TD>
                                            </TR>
                                            <TR>
                                                <TD class=ct width=666>
                                                    <div align=right><b class=cp>Recibo do Sacado</b>
                                                    </div>
                                                </TD>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width=666 cellspacing=5 cellpadding=0 border=0>
                                        <tr>
                                            <td width=41></td>
                                        </tr>
                                    </table>
                                    <table width=666 cellspacing=5 cellpadding=0 border=0 align=Default>
                                        <tr>
                                            <td class=ti width=455>
                                                <?php echo $dadosboleto["identificacao"]; ?> 
                                                <?php echo isset($dadosboleto["cpf_cnpj"]) ? "<br>".$dadosboleto["cpf_cnpj"] : '' ?><br>
	                                            <?php echo $dadosboleto["endereco"]; ?><br>
	                                            <?php echo $dadosboleto["cidade_uf"]; ?><br>
                                            </td>
                                            <td align=RIGHT width=150 class=ti>&nbsp;
                                            </td>
                                        </tr>
                                    </table>
                                    <BR>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tr>
                                            <td class=cp width=150> 
                                                <span class="campo">
                                                    <IMG src="/GED2.0/assets/img/boleto/logocaixa.jpg" width="150" height="40" border=0>
                                                </span>
                                            </td>
                                            <td width=3 valign=bottom>
                                                <img height=22 src="/GED2.0/assets/img/boleto/3.png" width=2 border=0>
                                            </td>
                                            <td class=cpt width=58 valign=bottom>
                                                <div align=center><font class=bc>
                                                    <?php echo $dadosboleto["codigo_banco_com_dv"]?>
                                                </font>
                                                </div>
                                            </td>
                                            <td width=3 valign=bottom>
                                                <img height=22 src="/GED2.0/assets/img/boleto/3.png" width=2 border=0>
                                            </td>
                                            <td class=ld align=right width=453 valign=bottom>
                                                <span class=ld> 
                                                    <span class="campotitulo">
                                                        <?php echo $dadosboleto["linha_digitavel"]?>
                                                    </span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tbody>
                                            <tr>
                                                <td colspan=5>
                                                    <img height=2 src="/GED2.0/assets/img/boleto/2.png" width=666 border=0></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=298 height=13>
                                                    Cedente
                                                </td>
                                                    <td class=ct valign=top width=7 height=13>
                                                        <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                    </td>
                                                <td class=ct valign=top width=126 height=13>
                                                    Agéncia/Código do Cedente
                                                </td>
                                                    <td class=ct valign=top width=7 height=13>
                                                        <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                    </td>
                                                <td class=ct valign=top width=34 height=13>
                                                    Espécie
                                                </td>
                                                    <td class=ct valign=top width=7 height=13>
                                                        <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                    </td>
                                                <td class=ct valign=top width=53 height=13>
                                                    Quantidade
                                                </td>
                                                    <td class=ct valign=top width=7 height=13>
                                                        <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                    </td>
                                                <td class=ct valign=top width=120 height=13>
                                                    Nosso número
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=298 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["cedente"]; ?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=126 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["agencia_codigo"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=34 height=12>
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["especie"]?>
                                                    </span> 
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=53 height=12>
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["quantidade"]?>
                                                    </span> 
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=120 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["nosso_numero"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=298 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=298 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png"" width=7 border=0>
                                                </td>
                                                <td valign=top width=126 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=126 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=34 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=34 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=53 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=53 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=120 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=120 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top colspan=3 height=13>
                                                    Número do documento
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=132 height=13>
                                                    CPF/CNPJ
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=134 height=13>
                                                    Vencimento
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>
                                                    Valor documento
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top colspan=3 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["numero_documento"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=132 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["cpf_cnpj"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=134 height=12> 
                                                    <span class="campo">
                                                        <?php echo ($data_venc != "") ? $dadosboleto["data_vencimento"] : "Contra Apresenta��o" ?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["valor_boleto"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=113 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=113 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=72 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=72 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=132 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=132 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=134 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=134 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=113 height=13>
                                                    (-) Desconto / Abatimentos
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=112 height=13>
                                                    (-) Outras deduções
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=113 height=13>
                                                    (+) Mora / Multa
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=113 height=13>
                                                    (+) Outros acréscimos
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>
                                                    (=) Valor cobrado
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=113 height=12></td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=112 height=12></td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=113 height=12></td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=113 height=12></td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12></td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=113 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=113 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=112 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=112 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=113 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=113 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=113 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=113 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=659 height=13>Sacado</td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=659 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["sacado"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=659 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=659 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct  width=7 height=12></td>
                                                <td class=ct  width=564 >
                                                    Demonstrativo
                                                </td>
                                                <td class=ct  width=7 height=12></td>
                                                <td class=ct  width=88 >
                                                    Autenticação mecánica
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  width=7 ></td><td class=cp width=564 >
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["demonstrativo1"]?><br>
                                                        <?php echo $dadosboleto["demonstrativo2"]?><br>
                                                        <?php echo $dadosboleto["demonstrativo3"]?><br>
                                                    </span>
                                                </td>
                                                <td  width=7 ></td>
                                                <td  width=88 ></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tbody>
                                            <tr>
                                                <td width=7></td>
                                                <td  width=500 class=cp> <br><br><br> 
                                            </td>
                                            <td width=159></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tr>
                                            <td class=ct width=666></td>
                                        </tr>
                                        <tbody>
                                            <tr>
                                                <td class=ct width=666> 
                                                    <div align=right>Corte na linha pontilhada</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=ct width=666>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/6.png" width=665 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tr>
                                            <td class=cp width=150> 
                                                <span class="campo"><IMG src="/GED2.0/assets/img/boleto/logocaixa.jpg" width="150" height="40" border=0></span>
                                            </td>
                                            <td width=3 valign=bottom>
                                                <img height=22 src="/GED2.0/assets/img/boleto/3.png" width=2 border=0>
                                            </td>
                                            <td class=cpt width=58 valign=bottom>
                                                <div align=center>
                                                    <font class=bc>
                                                        <?php echo $dadosboleto["codigo_banco_com_dv"]?>
                                                    </font>
                                                </div>
                                            </td>
                                            <td width=3 valign=bottom>
                                                <img height=22 src="/GED2.0/assets/img/boleto/3.png" width=2 border=0>
                                            </td>
                                            <td class=ld align=right width=453 valign=bottom>
                                                <span class=ld> 
                                                    <span class="campotitulo">
                                                        <?php echo $dadosboleto["linha_digitavel"]?>
                                                    </span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tbody>
                                            <tr>
                                                <td colspan=5>
                                                    <img height=2 src="/GED2.0/assets/img/boleto/2.png" width=666 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=472 height=13>
                                                    Local de pagamento
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>Vencimento</td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=472 height=12>
                                                    Pagável em qualquer Banco até o vencimento
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12> 
                                                    <span class="campo">
                                                        <?php echo ($data_venc != "") ? $dadosboleto["data_vencimento"] : "Contra Apresenta��o" ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=472 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=472 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=472 height=13>
                                                    Cedente
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>
                                                    Agéncia/Código cedente
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=472 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["cedente"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["agencia_codigo"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=472 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=472 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=113 height=13>
                                                    Data do documento
                                                </td>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=153 height=13>
                                                    N<u>o</u> documento
                                                </td>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=62 height=13>
                                                    Espécie doc.
                                                </td>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=34 height=13>
                                                    Aceite
                                                </td>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=82 height=13>
                                                    Data processamento
                                                </td>
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>
                                                    Nosso número
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=113 height=12>
                                                    <div align=left> 
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["data_documento"]?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=153 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["numero_documento"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=62 height=12>
                                                    <div align=left>
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["especie_doc"]?>
                                                        </span> 
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=34 height=12>
                                                    <div align=left>
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["aceite"]?>
                                                        </span> 
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=82 height=12>
                                                    <div align=left> 
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["data_processamento"]?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["nosso_numero"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=113 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=113 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=153 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=153 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=62 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=62 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=34 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=34 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=82 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=82 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr> 
                                                <td class=ct valign=top width=7 height=13> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top COLSPAN="3" height=13>
                                                    Uso do banco
                                                </td>
                                                <td class=ct valign=top height=13 width=7> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=83 height=13>
                                                    Carteira
                                                </td>
                                                <td class=ct valign=top height=13 width=7> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=53 height=13>
                                                    Espécie
                                                </td>
                                                <td class=ct valign=top height=13 width=7> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=123 height=13>
                                                    Quantidade
                                                </td>
                                                <td class=ct valign=top height=13 width=7> 
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=72 height=13> 
                                                    Valor Documento
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>(=) 
                                                    Valor documento
                                                </td>
                                            </tr>
                                            <tr> 
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td valign=top class=cp height=12 COLSPAN="3">
                                                    <div align=left> 
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=83> 
                                                    <div align=left> 
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["carteira"]?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=53>
                                                    <div align=left>
                                                        <span class="campo">
                                                            <?php echo $dadosboleto["especie"]?>
                                                        </span> 
                                                    </div>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=123>
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["quantidade"]?>
                                                    </span> 
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top  width=72> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["valor_unitario"]?>
                                                    </span>
                                                </td>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top align=right width=180 height=12> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["valor_boleto"]?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=75 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=31 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=31 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=83 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=83 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=53 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=53 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=123 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=123 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=72 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=72 border=0>
                                                </td>
                                                <td valign=top width=7 height=1> 
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody> 
                                    </table>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tbody>
                                            <tr>
                                                <td align=right width=10>
                                                    <table cellspacing=0 cellpadding=0 border=0 align=left>
                                                        <tbody> 
                                                            <tr> 
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td valign=top width=468 rowspan=5>
                                                    <font class=ct>
                                                        Instruções (Texto de responsabilidade do cedente)
                                                    </font><br>
                                                    <span class=cp> 
                                                        <FONT class=campo>
                                                            <?php echo $dadosboleto["instrucoes1"]; ?><br>
                                                            <?php echo $dadosboleto["instrucoes2"]; ?><br>
                                                            <?php echo $dadosboleto["instrucoes3"]; ?><br>
                                                            <?php echo $dadosboleto["instrucoes4"]; ?>
                                                        </FONT><br>
                                                    </span>
                                                </td>
                                                <td align=right width=188>
                                                    <table cellspacing=0 cellpadding=0 border=0>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=ct valign=top width=180 height=13>
                                                                    (-) Desconto / Abatimentos
                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=cp valign=top align=right width=180 height=12>
                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                                </td>
                                                                <td valign=top width=180 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr
                                            ><tr>
                                                <td align=right width=10> 
                                                    <table cellspacing=0 cellpadding=0 border=0 align=left>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign=top width=7 height=1> 
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td align=right width=188>
                                                    <table cellspacing=0 cellpadding=0 border=0>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=ct valign=top width=180 height=13>
                                                                    (-) Outras deduções
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12> 
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=cp valign=top align=right width=180 height=12>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                                </td>
                                                                <td valign=top width=180 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align=right width=10> 
                                                    <table cellspacing=0 cellpadding=0 border=0 align=left>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13> 
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td align=right width=188> 
                                                    <table cellspacing=0 cellpadding=0 border=0>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=ct valign=top width=180 height=13>
                                                                    (+) Mora / Multa
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=cp valign=top align=right width=180 height=12>

                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td valign=top width=7 height=1> 
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                                </td>
                                                                <td valign=top width=180 height=1> 
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align=right width=10>
                                                    <table cellspacing=0 cellpadding=0 border=0 align=left>
                                                        <tbody>
                                                            <tr> 
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td align=right width=188> 
                                                    <table cellspacing=0 cellpadding=0 border=0>
                                                        <tbody>
                                                            <tr> 
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=ct valign=top width=180 height=13>
                                                                    (+) Outros acréscimos
                                                                </td>
                                                            </tr>
                                                            <tr> 
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=cp valign=top align=right width=180 height=12></td>
                                                            </tr>
                                                            <tr>
                                                                <td valign=top width=7 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                                </td>
                                                                <td valign=top width=180 height=1>
                                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align=right width=10>
                                                    <table cellspacing=0 cellpadding=0 border=0 align=left>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td align=right width=188>
                                                    <table cellspacing=0 cellpadding=0 border=0>
                                                        <tbody>
                                                            <tr>
                                                                <td class=ct valign=top width=7 height=13>
                                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=ct valign=top width=180 height=13>
                                                                    (=) Valor cobrado
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class=cp valign=top width=7 height=12>
                                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                                </td>
                                                                <td class=cp valign=top align=right width=180 height=12></td>
                                                            </tr>
                                                        </tbody> 
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 width=666 border=0>
                                        <tbody>
                                            <tr>
                                                <td valign=top width=666 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=666 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=659 height=13>
                                                    Sacado
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=659 height=12>
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["sacado"]?>
                                                    </span> 
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=cp valign=top width=7 height=12>
                                                    <img height=12 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=659 height=12>
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["endereco1"]?>
                                                    </span> 
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table cellspacing=0 cellpadding=0 border=0>
                                        <tbody>
                                            <tr>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=cp valign=top width=472 height=13> 
                                                    <span class="campo">
                                                        <?php echo $dadosboleto["endereco2"]?>
                                                    </span>
                                                </td>
                                                <td class=ct valign=top width=7 height=13>
                                                    <img height=13 src="/GED2.0/assets/img/boleto/1.png" width=1 border=0>
                                                </td>
                                                <td class=ct valign=top width=180 height=13>
                                                    Cód. baixa
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=472 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=472 border=0>
                                                </td>
                                                <td valign=top width=7 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=7 border=0>
                                                </td>
                                                <td valign=top width=180 height=1>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/2.png" width=180 border=0>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <TABLE cellSpacing=0 cellPadding=0 border=0 width=666>
                                        <TBODY>
                                            <TR>
                                                <TD class=ct  width=7 height=12></TD>
                                                <TD class=ct  width=409 >
                                                    Sacador/Avalista
                                                </TD>
                                                <TD class=ct  width=250 >
                                                    <div align=right>
                                                        Autenticação mecánica - 
                                                        <b class=cp>
                                                            Ficha de Compensação
                                                        </b>
                                                    </div>
                                                </TD>
                                            </TR>
                                            <TR>
                                                <TD class=ct  colspan=3 ></TD>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <TABLE cellSpacing=0 cellPadding=0 width=666 border=0>
                                        <TBODY>
                                            <TR>
                                                <TD vAlign=bottom align=left height=70>
                                                    <?php fbarcode($dadosboleto["codigo_barras"]); ?>
                                                </TD>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <TABLE cellSpacing=0 cellPadding=0 width=666 border=0>
                                        <TR>
                                            <TD class=ct width=666></TD>
                                        </TR>
                                        <TBODY>
                                            <TR>
                                                <TD class=ct width=666>
                                                    <div align=right>
                                                        Corte na linha pontilhada
                                                    </div>
                                                </TD>
                                            </TR>
                                            <TR>
                                                <TD class=ct width=666>
                                                    <img height=1 src="/GED2.0/assets/img/boleto/6.png" width=665 border=0>
                                                </TD>
                                            </tr>
                                            <tr>
                                                <td class=ct width=666>
                                                    <center>
                                                    SAC CAIXA: 0800 726 0101 (informações, reclamações, sugestões e elogios)<br>
                                                    Para pessoas com deficiência auditiva ou de fala: 0800 726 2492<br>
                                                    Ouvidoria: 0800 725 7474<br>
                                                    caixa.gov.br</center>        
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </BODY>
                            </div>
                            <center>
                                <input type="button" value="Imprimir" 
                                onclick="window.print()" />
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</HTML>
