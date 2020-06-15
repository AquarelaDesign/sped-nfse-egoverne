<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Rps;
use NFePHP\NFSeEGoverne\Common\Soap\SoapFake;
//use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;
use NFePHP\NFSeEGoverne\Common\FakePretty;

try {
    
    // Teste em um ambiente real de produção
    $config = [
        'cnpj' => '03677669000291',
        'im' => '04068479',
        'cmun' => '4106902',
        'razao' => 'OPEN POINT VOLVO',
        'tpamb' => 1
    ];

    $configJson = json_encode($config);

    $content = file_get_contents('../../certs/03677669.pfx');
    $password = '03677669';
    $cert = Certificate::readPfx($content, $password);
    
    //$soap = new SoapFake();
    $soap = new SoapCurl($cert);
    $soap->disableCertValidation(false);
    
    $tools = new Tools($configJson, $cert);
    //$tools->loadSoapClass($soap);
    
    $arps = [];
    
    $std = new \stdClass();
    $std->version = '2.01';
    $std->IdentificacaoRps = new \stdClass();
    $std->IdentificacaoRps->Numero = 27548; //limite 15 digitos
    $std->IdentificacaoRps->Serie = 'RP'; //BH deve ser string numerico
    $std->IdentificacaoRps->Tipo = 1; //1 - RPS 2-Nota Fiscal Conjugada (Mista) 3-Cupom
    $std->DataEmissao = '2019-03-27T11:29:35';
    $std->NaturezaOperacao = 1; // 1 – Tributação no município
                                // 2 - Tributação fora do município
                                // 3 - Isenção
                                // 4 - Imune
                                // 5 – Exigibilidade suspensa por decisão judicial
                                // 6 – Exigibilidade suspensa por procedimento administrativo

    $std->RegimeEspecialTributacao = 1;    // 1 – Microempresa municipal
                                           // 2 - Estimativa
                                           // 3 – Sociedade de profissionais
                                           // 4 – Cooperativa
                                           // 5 – MEI – Simples Nacional
                                           // 6 – ME EPP – Simples Nacional

    $std->OptanteSimplesNacional = 2; //1 - SIM 2 - Não
    $std->IncentivadorCultural = 2; //1 - SIM 2 - Não
    $std->Status = 1;  // 1 – Normal  2 – Cancelado

    $std->Prestador = new \stdClass();
    $std->Prestador->Cnpj = "03677669000291";
    $std->Prestador->InscricaoMunicipal = "04068479";
    
    $std->Tomador = new \stdClass();
    //$std->Tomador->Cnpj = "99999999000191";
    $std->Tomador->Cpf = "27277970989";
    //$std->Tomador->InscricaoMunicipal = "";
    $std->Tomador->RazaoSocial = "ROSEMIR DO ROCIO FERREIRA VOSS";

    $std->Tomador->Endereco = new \stdClass();
    $std->Tomador->Endereco->Endereco = 'RUA JOAQUIM COSTA RIBEIRO';
    $std->Tomador->Endereco->Numero = '999';
    $std->Tomador->Endereco->Complemento = 'SEM COMP';
    $std->Tomador->Endereco->Bairro = 'BAIRRO ALTO';
    $std->Tomador->Endereco->CodigoMunicipio = 4106902;
    $std->Tomador->Endereco->Uf = 'PR';
    $std->Tomador->Endereco->Cep = 82840190;
    
    $std->Tomador->Contato = new \stdClass();
    $std->Tomador->Contato->Telefone = "41999999999";
    $std->Tomador->Contato->Email = "email@ig.com.br";

    $std->Servico = new \stdClass();
    $std->Servico->ItemListaServico = '1406';
    //$std->Servico->CodigoTributacaoMunicipio = '522310000';
    $std->Servico->Discriminacao = 'REM E INST VIDRO R$ 190.33\r\r\nA FATURA AGRUPA OS VALORES DAS NF:39548/055 E 27548/RP\r\nTOTAL DA FATURA 27063 R$ 4.045,85 - COM A(S) PARCELA(S):\r\nPARC: 27063/0101 - VENC: 26/04/19 - VALOR: 4.045,85\r\r\nTRIB APROX R$: 17,61 FED / 9,52 MUN /\r\nDESCONTO SERVICOS     19,67-\r\nO.S./TIPO: 055428 / GM KM:  8803 FAB: 2018\r\nCHASSI: LYVUZ10CCKB214051 PLACA: BEN0995\r\nMOD/VER: 246       - XC60 2.0 DR\r\nCPF/RG: 16166869878 / 124311730\r\nEMISSAO: 27/03/19 - 11:29:14 IMPRESSAO: 27/03/19 - 11:29:35 [ OFFOS ]\r\nVENDEDOR: 12011 - LUIS HENRIQUE BACCINELLO\r\nCONCES: OPEN POINT DIST. VEICULOS LTDA DT. VENDA: 29/11/18\r\n.';
    $std->Servico->CodigoMunicipio = 4106902;

    $std->Servico->Valores = new \stdClass();
    $std->Servico->Valores->ValorServicos = 190.33;
    $std->Servico->Valores->ValorDeducoes = 0.00;
    $std->Servico->Valores->ValorPis = 0.00;
    $std->Servico->Valores->ValorCofins = 0.00;
    $std->Servico->Valores->ValorInss = 0.00;
    $std->Servico->Valores->ValorIr = 0.00;
    $std->Servico->Valores->ValorCsll = 0.00;
    $std->Servico->Valores->IssRetido = 2;
    $std->Servico->Valores->ValorIss = 9.52;
    $std->Servico->Valores->ValorIssRetido = 0.00;
    $std->Servico->Valores->OutrasRetencoes = 0.00;
    $std->Servico->Valores->BaseCalculo = 190.33;
    $std->Servico->Valores->Aliquota = 0.05;
    $std->Servico->Valores->ValorLiquidoNfse = 190.33;
    $std->Servico->Valores->DescontoIncondicionado = 0.00;
    $std->Servico->Valores->DescontoCondicionado = 0.00;
    
    //$std->IntermediarioServico = new \stdClass();
    //$std->IntermediarioServico->RazaoSocial = 'INSCRICAO DE TESTE SIATU - D AGUA -PAULINO S'; 
    //$std->IntermediarioServico->Cnpj = '99999999000191';
    //$std->IntermediarioServico->InscricaoMunicipal = '8041700010';
    
    //$std->ConstrucaoCivil = new \stdClass();
    //$std->ConstrucaoCivil->CodigoObra = '1234';
    //$std->ConstrucaoCivil->Art = '1234';
    
    $arps[] = new Rps($std);
    
    $lote = '216289';
    $response = $tools->recepcionarLoteRps($arps, $lote);
    
    //print_r($response);
    echo FakePretty::prettyPrint($response, '');
 
} catch (\Exception $e) {
    echo $e->getMessage();
}