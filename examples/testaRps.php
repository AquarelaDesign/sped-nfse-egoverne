<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\NFSeEGoverne\Rps;

// Modelo Empresa de Curitiba/PR
$std = new \stdClass();
$std->version = '2.01';
$std->identificacaorps = new \stdClass();
$std->identificacaorps->numero = 27548; //limite 15 digitos
$std->identificacaorps->serie = 'RP'; //BH deve ser string numerico
$std->identificacaorps->tipo = 1; //1 - RPS 2-Nota Fiscal Conjugada (Mista) 3-Cupom
$std->dataemissao = '2019-03-27T11:29:35';
$std->naturezaoperacao = 1; // 1 – Tributação no município
                            // 2 - Tributação fora do município
                            // 3 - Isenção
                            // 4 - Imune
                            // 5 – Exigibilidade suspensa por decisão judicial
                            // 6 – Exigibilidade suspensa por procedimento administrativo

$std->regimeespecialtributacao = 1;    // 1 – Microempresa municipal
                                       // 2 - Estimativa
                                       // 3 – Sociedade de profissionais
                                       // 4 – Cooperativa
                                       // 5 – MEI – Simples Nacional
                                       // 6 – ME EPP – Simples Nacional

$std->optantesimplesnacional = 2; //1 - SIM 2 - Não
$std->incentivadorcultural = 1; //1 - SIM 2 - Não
$std->status = 1;  // 1 – Normal  2 – Cancelado

$std->tomador = new \stdClass();
//$std->tomador->cnpj = "99999999000191";
$std->tomador->cpf = "27277970989";
$std->tomador->inscricaomunicipal = "";
$std->tomador->razaosocial = "ROSEMIR DO ROCIO FERREIRA VOSS";

$std->tomador->endereco = new \stdClass();
$std->tomador->endereco->endereco = 'RUA JOAQUIM COSTA RIBEIRO';
$std->tomador->endereco->numero = '999';
$std->tomador->endereco->complemento = 'SEM COMP';
$std->tomador->endereco->bairro = 'BAIRRO ALTO';
$std->tomador->endereco->codigomunicipio = 4106902;
$std->tomador->endereco->uf = 'PR';
$std->tomador->endereco->cep = 82840190;

$std->tomador->contato = new \stdClass();
$std->tomador->contato->telefone = "41999999999";
$std->tomador->contato->email = "email@ig.com.br";

$std->Servico = new \stdClass();
$std->Servico->itemlistaServico = '1406';
//$std->Servico->codigotributacaomunicipio = '522310000';
//$std->Servico->codigocnae = '4520001';
$std->Servico->discriminacao = 'REM E INST VIDRO R$ 190.33\r\r\nA FATURA AGRUPA OS VALORES DAS NF:39548/055 E 27548/RP\r\nTOTAL DA FATURA 27063 R$ 4.045,85 - COM A(S) PARCELA(S):\r\nPARC: 27063/0101 - VENC: 26/04/19 - VALOR: 4.045,85\r\r\nTRIB APROX R$: 17,61 FED / 9,52 MUN /\r\nDESCONTO SERVICOS     19,67-\r\nO.S./TIPO: 055428 / GM KM:  8803 FAB: 2018\r\nCHASSI: LYVUZ10CCKB214051 PLACA: BEN0995\r\nMOD/VER: 246       - XC60 2.0 DR\r\nCPF/RG: 16166869878 / 124311730\r\nEMISSAO: 27/03/19 - 11:29:14 IMPRESSAO: 27/03/19 - 11:29:35 [ OFFOS ]\r\nVENDEDOR: 12011 - LUIS HENRIQUE BACCINELLO\r\nCONCES: OPEN POINT DIST. VEICULOS LTDA DT. VENDA: 29/11/18\r\n.';
$std->Servico->codigomunicipio = 4106902;

$std->Servico->Valores = new \stdClass();
$std->Servico->Valores->ValorServicos = 190.33;
$std->Servico->Valores->Valordeducoes = 0.00;
$std->Servico->Valores->Valorpis = 0.00;
$std->Servico->Valores->Valorcofins = 0.00;
$std->Servico->Valores->Valorinss = 0.00;
$std->Servico->Valores->Valorir = 0.00;
$std->Servico->Valores->Valorcsll = 0.00;
$std->Servico->Valores->Issretido = 2;
$std->Servico->Valores->Valoriss = 9.52;
$std->Servico->Valores->ValorIssretido = 0.00;
$std->Servico->Valores->Outrasretencoes = 0.00;
$std->Servico->Valores->BaseCalculo = 190.33;
$std->Servico->Valores->Aliquota = 0.05;
$std->Servico->Valores->Valorliquidonfse = 190.33;
$std->Servico->Valores->Descontoincondicionado = 0.00;
$std->Servico->Valores->Descontocondicionado = 0.00;

//$std->Intermediarioservico = new \stdClass();
//$std->Intermediarioservico->RazaoSocial = 'INSCRICAO DE TESTE SIATU - D AGUA -PAULINO S'; 
//$std->Intermediarioservico->Cnpj = '99999999000191';
//$std->Intermediarioservico->InscricaoMunicipal = '8041700010';

//$std->construcaocivil = new \stdClass();
//$std->construcaocivil->codigoobra = '1234';
//$std->construcaocivil->art = '1234';


$rps = new Rps($std);

header("Content-type: text/xml");
echo $rps->render();

/*
echo "<pre>";
print_r(json_encode($std));
echo "</pre>";
*/

