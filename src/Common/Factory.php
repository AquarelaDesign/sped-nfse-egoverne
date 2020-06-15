<?php

namespace NFePHP\NFSeEGoverne\Common;

/**
 * Class for RPS XML convertion
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

use stdClass;
use NFePHP\Common\DOMImproved as Dom;
use DOMNode;
use DOMElement;

class Factory
{
    /**
     * @var stdClass
     */
    protected $std;
    /**
     * @var Dom
     */
    protected $dom;
    /**
     * @var DOMNode
     */
    protected $rps;

    /**
     * Constructor
     * @param stdClass $std
     */
    public function __construct(stdClass $std)
    {
        $this->std = $std;
        
        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
        $this->rps = $this->dom->createElement('Rps');
    }
    
    /**
     * Builder, converts sdtClass Rps in XML Rps
     * NOTE: without Prestador Tag
     * @return string RPS in XML string format
     */
    public function render()
    {
        $infRps = $this->dom->createElement('InfRps');
        
        if ($this->std->servico->codigomunicipio != 4106902) {
           $att = $this->dom->createAttribute('Id');
           $att->value = 'ID' . $this->std->identificacaorps->numero;
           $infRps->appendChild($att);
        }
        
        $this->addIdentificacao($infRps);
        
        if (floatval($this->std->version) <= 2.01) {
           $this->dom->addChild(
               $infRps,
               "DataEmissao",
               $this->std->dataemissao,
               true
           );
           $this->dom->addChild(
               $infRps,
               "NaturezaOperacao",
               $this->std->naturezaoperacao,
               true
           );
           $this->dom->addChild(
               $infRps,
               "RegimeEspecialTributacao",
               $this->std->regimeespecialtributacao,
               true
           );
           $this->dom->addChild(
               $infRps,
               "OptanteSimplesNacional",
               $this->std->optantesimplesnacional,
               true
           );
           $this->dom->addChild(
               $infRps,
               "IncentivadorCultural",
               $this->std->incentivadorcultural,
               false
           );
           $this->dom->addChild(
               $infRps,
               "Status",
               $this->std->status,
               true
           );
        } else {
            $this->dom->addChild(
                $infRps,
                "Competencia",
                isset($this->std->competencia)
                    ? $this->std->competencia
                    : null,
                true
            );
        }
        
        $this->addServico($infRps);
        $this->addPrestador($infRps);
        $this->addTomador($infRps);
        $this->addIntermediario($infRps);
        $this->addConstrucao($infRps);
        
        if (floatval($this->std->version) > 2.01) {
           $this->dom->addChild(
               $infRps,
               "RegimeEspecialTributacao",
               $this->std->regimeespecialtributacao,
               true
           );
           $this->dom->addChild(
               $infRps,
               "OptanteSimplesNacional",
               $this->std->optantesimplesnacional,
               true
           );
           $this->dom->addChild(
               $infRps,
               "IncentivoFiscal",
               $this->std->incentivofiscal,
               true
           );
        }

        $this->rps->appendChild($infRps);
        $this->dom->appendChild($this->rps);
        return $this->dom->saveXML();
    }
    
    /**
     * Includes Identificacao TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addIdentificacao(&$parent)
    {
        if (floatval($this->std->version) > 2.01) {
            $rps = $this->dom->createElement('Rps');
        }

        $id = $this->std->identificacaorps;
        $node = $this->dom->createElement('IdentificacaoRps');
        $this->dom->addChild(
            $node,
            "Numero",
            $id->numero,
            true
        );
        $this->dom->addChild(
            $node,
            "Serie",
            $id->serie,
            true
        );
        $this->dom->addChild(
            $node,
            "Tipo",
            $id->tipo,
            true
        );

        if (floatval($this->std->version) > 2.01) {
            $rps->appendChild($node);
            $this->dom->addChild(
                $rps,
                "DataEmissao",
                $this->std->dataemissao,
                true
            );
            $this->dom->addChild(
                $rps,
                "Status",
                $this->std->status,
                true
            );
            $parent->appendChild($rps);
        } else {
            $parent->appendChild($node);
        }

    }
    
    /**
     * Includes Servico TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addServico(&$parent)
    {
        $serv = $this->std->servico;
        $val = $this->std->servico->valores;
        $node = $this->dom->createElement('Servico');
        $valnode = $this->dom->createElement('Valores');
        $this->dom->addChild(
            $valnode,
            "ValorServicos",
            number_format($val->valorservicos, 2, '.', ''),
            true
        );
        $this->dom->addChild(
            $valnode,
            "ValorDeducoes",
            isset($val->valordeducoes)
                ? number_format($val->valordeducoes, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorPis",
            isset($val->valorpis)
                ? number_format($val->valorpis, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorCofins",
            isset($val->valorcofins)
                ? number_format($val->valorcofins, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorInss",
            isset($val->valorinss)
                ? number_format($val->valorinss, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorIr",
            isset($val->valorir)
                ? number_format($val->valorir, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorCsll",
            isset($val->valorcsll)
                ? number_format($val->valorcsll, 2, '.', '')
                : null,
            false
        );
        
        if (floatval($this->std->version) <= 2.01) {
            $this->dom->addChild(
                $valnode,
                "IssRetido",
                isset($val->issretido) ? $val->issretido : null,
                false
            );
        }
        
        $this->dom->addChild(
            $valnode,
            "ValorIss",
            isset($val->valoriss)
                ? number_format($val->valoriss, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorIssRetido",
            isset($val->valorissretido)
                ? number_format($val->valorissretido, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "OutrasRetencoes",
            isset($val->outrasretencoes)
                ? number_format($val->outrasretencoes, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "BaseCalculo",
            isset($val->basecalculo)
                ? number_format($val->basecalculo, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "Aliquota",
            isset($val->aliquota) ? $val->aliquota : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "ValorLiquidoNfse",
            isset($val->valorliquidonfse)
                ? number_format($val->valorliquidonfse, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "DescontoIncondicionado",
            isset($val->descontoincondicionado)
                ? number_format($val->descontoincondicionado, 2, '.', '')
                : null,
            false
        );
        $this->dom->addChild(
            $valnode,
            "DescontoCondicionado",
            isset($val->descontocondicionado)
                ? number_format($val->descontocondicionado, 2, '.', '')
                : null,
            false
        );
        $node->appendChild($valnode);
        
        if (floatval($this->std->version) > 2.01) {
            $this->dom->addChild(
                $node,
                "IssRetido",
                isset($val->issretido) ? $val->issretido : null,
                false
            );
        }

        $this->dom->addChild(
            $node,
            "ItemListaServico",
            $serv->itemlistaservico,
            true
        );
        $this->dom->addChild(
            $node,
            "CodigoTributacaoMunicipio",
            isset($serv->codigotributacaomunicipio)
                ? $serv->codigotributacaomunicipio
                : null,
            true
        );
        $this->dom->addChild(
            $node,
            "Discriminacao",
            $serv->discriminacao,
            true
        );
        $this->dom->addChild(
            $node,
            "CodigoMunicipio",
            $serv->codigomunicipio,
            true
        );
        $this->dom->addChild(
            $node,
            "ExigibilidadeISS",
            isset($serv->exigibilidadeiss)
                ? $serv->exigibilidadeiss
                : null,
            true
        );

        $parent->appendChild($node);
    }
    
    /**
     * Includes Prestador TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addPrestador(&$parent)
    {
        if (!isset($this->std->prestador)) {
            return;
        }
        $pre = $this->std->prestador;
        
        $node = $this->dom->createElement('Prestador');
        
        if (isset($pre->cnpj)) {
            $this->dom->addChild(
                $node,
                "Cnpj",
                isset($pre->cnpj) ? $pre->cnpj : null,
                false
            );
        } else {
            $this->dom->addChild(
                $node,
                "Cpf",
                isset($pre->cpf) ? $pre->cpf : null,
                false
            );
        }
        
        $this->dom->addChild(
            $node,
            "InscricaoMunicipal",
            isset($pre->inscricaomunicipal) ? $pre->inscricaomunicipal : null,
            false
        );
        
        $parent->appendChild($node);
    }
    
    /**
     * Includes Tomador TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addTomador(&$parent)
    {
        if (!isset($this->std->tomador)) {
            return;
        }
        $tom = $this->std->tomador;
        $end = $this->std->tomador->endereco;
        
        $node = $this->dom->createElement('Tomador');
        $ide = $this->dom->createElement('IdentificacaoTomador');
        $cpfcnpj = $this->dom->createElement('CpfCnpj');
        if (isset($tom->cnpj)) {
            $this->dom->addChild(
                $cpfcnpj,
                "Cnpj",
                $tom->cnpj,
                true
            );
        } else {
            $this->dom->addChild(
                $cpfcnpj,
                "Cpf",
                $tom->cpf,
                true
            );
        }
        $ide->appendChild($cpfcnpj);
        
        $this->dom->addChild(
            $ide,
            "InscricaoMunicipal",
            isset($tom->inscricaomunicipal) ? $tom->inscricaomunicipal : null,
            true
        );
        $node->appendChild($ide);
        $this->dom->addChild(
            $node,
            "RazaoSocial",
            $tom->razaosocial,
            true
        );
        $endereco = $this->dom->createElement('Endereco');
        $this->dom->addChild(
            $endereco,
            "Endereco",
            $end->endereco,
            true
        );
        $this->dom->addChild(
            $endereco,
            "Numero",
            $end->numero,
            true
        );
        $this->dom->addChild(
            $endereco,
            "Complemento",
            isset($end->complemento) ? $end->complemento : null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Bairro",
            $end->bairro,
            true
        );
        $this->dom->addChild(
            $endereco,
            "CodigoMunicipio",
            $end->codigomunicipio,
            true
        );
        $this->dom->addChild(
            $endereco,
            "Uf",
            $end->uf,
            true
        );
        $this->dom->addChild(
            $endereco,
            "CodigoPais",
            isset($end->codigopais) ? $end->codigopais : null,
            false
        );
        $this->dom->addChild(
            $endereco,
            "Cep",
            $end->cep,
            true
        );
        $node->appendChild($endereco);

        if (isset($tom->contato)) {
            $con = $this->std->tomador->contato;
            $contato = $this->dom->createElement('Contato');
            $this->dom->addChild(
                $contato,
                "Telefone",
                isset($con->telefone) ? $con->telefone : null,
                false
            );
            $this->dom->addChild(
                $contato,
                "Email",
                isset($con->email) ? $con->email : null,
                false
            );
            $node->appendChild($contato);
        }

        $parent->appendChild($node);
    }
    
    /**
     * Includes Intermediario TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addIntermediario(&$parent)
    {
        if (!isset($this->std->intermediarioservico)) {
            return;
        }
        $int = $this->std->intermediarioservico;
        $node = $this->dom->createElement('IntermediarioServico');
        $this->dom->addChild(
            $node,
            "RazaoSocial",
            $int->razaosocial,
            true
        );
        $cpfcnpj = $this->dom->createElement('CpfCnpj');
        if (isset($int->cnpj)) {
            $this->dom->addChild(
                $cpfcnpj,
                "Cnpj",
                $int->cnpj,
                true
            );
        } else {
            $this->dom->addChild(
                $cpfcnpj,
                "Cpf",
                $int->cpf,
                true
            );
        }
        $node->appendChild($cpfcnpj);
        $this->dom->addChild(
            $node,
            "InscricaoMunicipal",
            $int->inscricaomunicipal,
            false
        );
        $parent->appendChild($node);
    }
    
    /**
     * Includes Construcao TAG in parent NODE
     * @param DOMNode $parent
     */
    protected function addConstrucao(&$parent)
    {
        if (!isset($this->std->construcaocivil)) {
            return;
        }
        $obra = $this->std->construcaocivil;
        $node = $this->dom->createElement('ConstrucaoCivil');
        $this->dom->addChild(
            $node,
            "CodigoObra",
            $obra->codigoobra,
            true
        );
        $this->dom->addChild(
            $node,
            "Art",
            $obra->art,
            true
        );
        $parent->appendChild($node);
    }
}
