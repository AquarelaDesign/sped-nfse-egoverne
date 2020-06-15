<?php

namespace NFePHP\NFSeEGoverne;

/**
 * Class for comunications with NFSe webserver in Nacional Standard
 *
 * @category  NFePHP
 * @package   NFePHP\NFSeEGoverne
 * @copyright NFePHP Copyright (c) 2008-2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse-egoverne for the canonical source repository
 */

use NFePHP\NFSeEGoverne\Common\Tools as BaseTools;
use NFePHP\NFSeEGoverne\RpsInterface;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Certificate;
use NFePHP\Common\Validator;

class Tools extends BaseTools
{
    const ERRO_EMISSAO = 1;
    const SERVICO_NAO_CONCLUIDO = 2;
    
    protected $xsdpath;
    
    public function __construct($config, Certificate $cert)
    {
        $this->config = json_decode(json_encode($config));
        parent::__construct($config, $cert);
        $path = realpath(__DIR__ . '/../storage/schemes');
        
        if (file_exists($this->xsdpath = $path . '/'.$this->config->cmun.'.xsd')) {
            $this->xsdpath = $path . '/'.$this->config->cmun.'.xsd';
        } else {
            $this->xsdpath = $path . '/nfse_v20_08_2015.xsd';
        }
    }
    
    /**
     * Solicita o cancelamento de NFSe (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=CancelarNfse
     * @param string $id
     * @param integer $numero
     * @param integer $codigo
     * @return string
     */
    public function cancelarNfse($id, $numero, $codigo = self::ERRO_EMISSAO)
    {
        $pedido = $content = '';
        $operation = 'CancelarNfse';
        
        $pedido .= "<Pedido>";
        $pedido .=     "<InfPedidoCancelamento>";
        $pedido .=         "<IdentificacaoNfse>";
        $pedido .=             "<Numero>{$numero}</Numero>";
        $pedido .=             "<Cnpj>{$this->config->cnpj}</Cnpj>";
        $pedido .=             "<InscricaoMunicipal>{$this->config->im}</InscricaoMunicipal>";
        $pedido .=             "<CodigoMunicipio>{$this->config->cmun}</CodigoMunicipio>";
        $pedido .=         "</IdentificacaoNfse>";
        $pedido .=         "<CodigoCancelamento>{$codigo}</CodigoCancelamento>";
        $pedido .=     "</InfPedidoCancelamento>";
        $pedido .= "</Pedido>";
                                        
        $signed = $this->sign($pedido, 'InfPedidoCancelamento', '');
        
        $content .= "<CancelarNfseEnvio xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<CancelarNfseEnvio>{$signed}</CancelarNfseEnvio>";
        $content .= "</CancelarNfseEnvio>";
        
        Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta Lote RPS (SINCRONO) após envio com recepcionarLoteRps() (ASSINCRONO)
     * complemento do processo de envio assincono.
     * Que deve ser usado quando temos mais de um RPS sendo enviado
     * por vez.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarLoteRps
     * @param string $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo)
    {
        $content = '';
        $operation = 'ConsultarLoteRps';
        
        //$content .= "<ConsultarLoteRps xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<ConsultarLoteRpsEnvio>";
        $content .=         $this->prestador;
        $content .=         "<Protocolo>{$protocolo}</Protocolo>";
        $content .=     "</ConsultarLoteRpsEnvio>";
        //$content .= "</ConsultarLoteRps>";
        
        //Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta NFSe emitidas em um periodo e por tomador (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfse
     * @param string $dini
     * @param string $dfim
     * @param string $tomadorCnpj
     * @param string $tomadorCpf
     * @param string $tomadorIM
     * @return string
     */
    public function consultarNfse(
        $dini,
        $dfim,
        $tomadorCnpj = null,
        $tomadorCpf = null,
        $tomadorIM = null,
        $numeroNFSe = null,
        $intermediario = null
    ) {
        $content = '';
        $operation = 'ConsultarNfse';
        
        $content .= "<ConsultarNfse xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<ConsultarNfseEnvio>";
        $content .=         $this->prestador;
        $content .=         "<NumeroNfse>{$numeroNFSe}<NumeroNfse>";
        $content .=         "<PeriodoEmissao>";
        $content .=             "<DataInicial>{$dini}</DataInicial>";
        $content .=             "<DataFinal>{$dfim}</DataFinal>";
        $content .=         "</PeriodoEmissao>";
        
        if ($tomadorCnpj !== null || $tomadorCpf !== null) {
            if (isset($tomadorCnpj)) {
                $tomadorDocumento = "<Cnpj>{$tomadorCnpj}</Cnpj>";
            } else {
                $tomadorDocumento = "<Cpf>{$tomadorCpf}</Cpf>";
            }
            
            $tomadorInscMun = '';
            if (isset($tomadorIM)) {
                $tomadorInscMun = "<InscricaoMunicipal>{$tomadorIM}</InscricaoMunicipal>";
            }
            
            $content .= "<Tomador>";
            $content .=     "<CpfCnpj>";
            $content .=         $tomadorDocumento;
            $content .=     "</CpfCnpj>";
            $content .=     $tomadorInscMun;
            $content .= "</Tomador>";
        }
                
        $content .=         $intermediario;
        $content .=     "</ConsultarNfseEnvio>";
        $content .= "</ConsultarNfse>";
 
        Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
    
    /**
     * Consulta NFSe por RPS (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfsePorRps
     * @param integer $numero
     * @param string $serie
     * @param integer $tipo
     * @return string
     */
    public function consultarNfsePorRps($numero, $serie, $tipo)
    {
        $content = '';
        $operation = "ConsultarNfsePorRps";
        
        $content .= "<ConsultarNfseRpsEnvio xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<IdentificacaoRps>";
        $content .=         "<Numero>{$numero}</Numero>";
        $content .=         "<Serie>{$serie}</Serie>";
        $content .=         "<Tipo>{$tipo}</Tipo>";
        $content .=     "</IdentificacaoRps>";
        $content .=     $this->prestador;
        $content .= "</ConsultarNfseRpsEnvio>";
   
        Validator::isValid($content, $this->xsdpath);
                
        return $this->send($content, $operation);
    }
    
    /**
     * Envia LOTE de RPS para emissão de NFSe (ASSINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarLoteRps
     * @param array $arps Array contendo de 1 a 50 RPS::class
     * @param string $lote Número do lote de envio
     * @return string
     * @throws \Exception
     */
    public function recepcionarLoteRps($arps, $lote) 
    {
        $content = $listaRpsContent = '';
        $operation = 'RecepcionarLoteRps';
        
        $countRps = count($arps);
        if ($countRps > 50) {
            throw new \Exception('O limite é de 50 RPS por lote enviado.');
        }
        
        foreach ($arps as $rps) {
            $xml = $rps->render();
            $xmlsigned = $this->sign($xml, 'InfRps', '');
            $listaRpsContent .= $xmlsigned;
        }

        $content .= "<EnviarLoteRpsEnvio xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<LoteRps Id=\"ID{$lote}\">";
        $content .=         "<NumeroLote>{$lote}</NumeroLote>";
        $content .=         "<Cnpj>{$this->config->cnpj}</Cnpj>";
        $content .=         "<InscricaoMunicipal>{$this->config->im}</InscricaoMunicipal>";
        $content .=         "<QuantidadeRps>{$countRps}</QuantidadeRps>";
        $content .=         "<ListaRps>";
        $content .=             $listaRpsContent;
        $content .=         "</ListaRps>";
        $content .=     "</LoteRps>";
        $content .= "</EnviarLoteRpsEnvio>";
                                
        $content = $this->sign($content, 'LoteRps', '');
        //Validator::isValid($content, $this->xsdpath);
        
        try {
            return $this->send($content, $operation);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }
    
    /**
     * Buscar Usuario (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=BuscarUsuario
     * @param string $cnpj
     * @param string $imu
     * @return string
     */
    public function buscarUsuario($cnpj, $imu)
    {
        $content = '';
        $operation = 'BuscarUsuario';
        
        $content .= "<BuscarUsuario xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<imu>{$imu}</imu>";
        $content .=     "<cnpj>{$cnpj}</cnpj>";
        $content .= "</BuscarUsuario>";
        
        Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
    
    /**
     * Recepcionar Xml (SINCRONO)
     * Parâmentro (metodo) nome do metodo WS que será chamado.
     * Os valores podem ser : (RecepcionarLoteRps, ConsultarSituacaoLoteRps, ConsultarNfsePorRps,
     *                         ConsultarNfse, ConsultarLoteRps e CancelarNfse)
     * e o Parâmetro (xml) deve ser a mensagem xml a ser enviada.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarXml
     * @param string $metodo
     * @param string $xml
     * @return string
     */
    public function recepcionarXml($metodo, $xml)
    {
        $content = '';
        $operation = 'RecepcionarXml';
        
        $content .= "<RecepcionarXml xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<metodo>{$metodo}</metodo>";
        $content .=     "<xml>{$xml}</xml>";
        $content .= "</RecepcionarXml>";
        
        Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
    
    /**
     * Validar Xml (SINCRONO)
     * Realiza a validação básica de um xml de acordo com o schema xsd
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ValidarXml
     * @param string $xml
     * @return string
     */
    public function validarXml($xml)
    {
        $content = '';
        $operation = 'ValidarXml';
        
        $content .= "<ValidarXml xmlns=\"{$this->wsobj->msgns}\">";
        $content .=     "<xml>$xml</xml>";
        $content .= "</ValidarXml>";
        
        //$dom = new Dom('1.0', 'UTF-8');
        //$dom->preserveWhiteSpace = false;
        //$dom->formatOutput = false;
        //$dom->loadXML($contdata);
        
        //$node = $dom->getElementsByTagName('xml')->item(0);
        //$cdata = $dom->createCDATASection($xml);
        //$node->appendChild($cdata);
        
        //$content = $dom->saveXML($dom->documentElement);
        //Validator::isValid($content, $this->xsdpath);
        
        return $this->send($content, $operation);
    }
}
