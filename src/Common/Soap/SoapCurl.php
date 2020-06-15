<?php

namespace NFePHP\NFSeEGoverne\Common\Soap;

/**
 * SoapClient based in cURL class
 *
 * @category  NFePHP
 * @package   NFePHP\NFSeEGoverne
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse-egoverne for the canonical source repository
 */

use NFePHP\NFSeEGoverne\Common\Soap\SoapBase;
use NFePHP\NFSeEGoverne\Common\Soap\SoapInterface;
use NFePHP\Common\Exception\SoapException;
use NFePHP\Common\Certificate;
use Psr\Log\LoggerInterface;

class SoapCurl extends SoapBase implements SoapInterface
{
    
    protected $http_codes = [
        '100' => "Continue",
        '101' => "Switching Protocols",
        '200' => "OK",
        '201' => "Created",
        '202' => "Accepted",
        '203' => "Non-Authoritative Information",
        '204' => "No Content",
        '205' => "Reset Content",
        '206' => "Partial Content",
        '300' => "Multiple Choices",
        '301' => "Moved Permanently",
        '302' => "Found",
        '303' => "See Other",
        '304' => "Not Modified",
        '305' => "Use Proxy",
        '306' => "(Unused)",
        '307' => "Temporary Redirect",
        '400' => "Bad Request",
        '401' => "Unauthorized",
        '402' => "Payment Required",
        '403' => "Forbidden",
        '404' => "Not Found",
        '405' => "Method Not Allowed",
        '406' => "Not Acceptable",
        '407' => "Proxy Authentication Required",
        '408' => "Request Timeout",
        '409' => "Conflict",
        '410' => "Gone",
        '411' => "Length Required",
        '412' => "Precondition Failed",
        '413' => "Request Entity Too Large",
        '414' => "Request-URI Too Long",
        '415' => "Unsupported Media Type",
        '416' => "Requested Range Not Satisfiable",
        '417' => "Expectation Failed",
        '500' => "Internal Server Error",
        '501' => "Not Implemented",
        '502' => "Bad Gateway",
        '503' => "Service Unavailable",
        '504' => "Gateway Timeout",
        '505' => "HTTP Version Not Supported"
    ];
    
    /**
     * Constructor
     * @param Certificate $certificate
     * @param LoggerInterface $logger
     */
    public function __construct(Certificate $certificate = null, LoggerInterface $logger = null)
    {
        parent::__construct($certificate, $logger);
    }
    
    /**
     * Send soap message to url
     * @param string $operation
     * @param string $url
     * @param string $action
     * @param string $envelope
     * @param array $parameters
     * @return string
     * @throws \NFePHP\Common\Exception\SoapException
     */
    public function send(
        $operation,
        $url,
        $action,
        $envelope,
        $parameters
    ) {
        $response = '';
        $this->requestHead = implode("\n", $parameters);
        $this->requestBody = $envelope;
        
        try {
            $this->saveTemporarilyKeyFiles();
            $oCurl = curl_init();
            $this->setCurlProxy($oCurl);
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soaptimeout);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soaptimeout + 40);
            curl_setopt($oCurl, CURLOPT_HEADER, 1);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
            if (!$this->disablesec) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
                if (is_file($this->casefaz)) {
                    curl_setopt($oCurl, CURLOPT_CAINFO, $this->casefaz);
                }
            }
            curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->soapprotocol);
            curl_setopt($oCurl, CURLOPT_SSLCERT, $this->tempdir . $this->certfile);
            curl_setopt($oCurl, CURLOPT_SSLKEY, $this->tempdir . $this->prifile);
            if (!empty($this->temppass)) {
                curl_setopt($oCurl, CURLOPT_KEYPASSWD, $this->temppass);
            }
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
            if (! empty($envelope)) {
                curl_setopt($oCurl, CURLOPT_POST, true);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parameters);
            }
            $response = curl_exec($oCurl);
            $this->soaperror = curl_error($oCurl);
            $ainfo = curl_getinfo($oCurl);
            if (is_array($ainfo)) {
                $this->soapinfo = $ainfo;
            }
            $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
            $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
            curl_close($oCurl);
            $this->responseHead = trim(substr($response, 0, $headsize));
            $this->responseBody = trim(substr($response, $headsize));
            $this->saveDebugFiles(
                $operation,
                $this->requestHead . "\n" . $this->requestBody,
                $this->responseHead . "\n" . $this->responseBody
            );
        } catch (\Exception $e) {
            throw SoapException::unableToLoadCurl($e->getMessage());
        }
        if ($this->soaperror != '') {
            throw SoapException::soapFault($this->soaperror . " [$url]");
        }
        if ($httpcode != 200) {
            return "[$url] HTTP Error code: $httpcode - " . $this->http_codes[$httpcode];
            //throw SoapException::soapFault(
                //" [$url] HTTP Error code: $httpcode - " . $this->http_codes[$httpcode]
                //. $this->getFaultString($this->responseBody)
                //. " || requestHead: $this->requestHead" . PHP_EOL . PHP_EOL
                //. " || requestBody: $this->requestBody" . PHP_EOL
            //);
        }

        //return $this->responseBody . ' || ' . $this->requestBody;
        return $this->responseBody;
    }
    
    /**
     * Recover WSDL form given URL
     * @param string $url
     * @return string
     */
    public function wsdl($url)
    {
        $response = '';
        $this->saveTemporarilyKeyFiles();
        $url .= '?Wsdl'; //singleWsdl
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soaptimeout);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soaptimeout + 20);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->soapprotocol);
        curl_setopt($oCurl, CURLOPT_SSLCERT, $this->tempdir . $this->certfile);
        curl_setopt($oCurl, CURLOPT_SSLKEY, $this->tempdir . $this->prifile);
        if (!empty($this->temppass)) {
            curl_setopt($oCurl, CURLOPT_KEYPASSWD, $this->temppass);
        }
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($oCurl);
        $soaperror = curl_error($oCurl);
        $ainfo = curl_getinfo($oCurl);
        $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);
        if ($httpcode != 200) {
            return '';
        }
        return $response;
    }
    
    /**
     * Set proxy into cURL parameters
     * @param resource $oCurl
     */
    private function setCurlProxy(&$oCurl)
    {
        if ($this->proxyIP != '') {
            curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($oCurl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($oCurl, CURLOPT_PROXY, $this->proxyIP.':'.$this->proxyPort);
            if ($this->proxyUser != '') {
                curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->proxyUser.':'.$this->proxyPass);
                curl_setopt($oCurl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            }
        }
    }
    
    /**
     * Extract faultstring form response if exists
     * @param string $body
     * @return string
     */
    private function getFaultString($body)
    {
        if (empty($body)) {
            return '';
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($body);
        $faultstring = '';
        $nodefault = !empty($dom->getElementsByTagName('faultstring')->item(0))
            ? $dom->getElementsByTagName('faultstring')->item(0)
            : '';
        if (!empty($nodefault)) {
            $faultstring = $nodefault->nodeValue;
        }
        return htmlentities($faultstring, ENT_QUOTES, 'UTF-8');
    }
}
