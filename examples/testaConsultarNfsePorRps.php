<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\Tools;
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
    
    $soap = new SoapFake();
    //$soap = new SoapCurl($cert);
    $soap->disableCertValidation(true);
    
    $tools = new Tools($configJson, $cert);
    $tools->loadSoapClass($soap);

    $numero = 27548;
    $serie = 'RP';
    $tipo = 1;

    $response = $tools->consultarNfsePorRps($numero, $serie, $tipo);
    
    //echo $response;
    echo FakePretty::prettyPrint($response, '');
 
} catch (\Exception $e) {
    echo $e->getMessage();
}
