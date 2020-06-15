<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once 'vendor/autoload.php';

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

$modelo = 'abrasf';
$version = '2_01';

$jsonSchema = buscaSchema($modelo, $version);


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

$std->regimeespecialtributacao = 2;    // 1 – Microempresa municipal
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
$std->tomador->cpf = "16166869878";
$std->tomador->razaosocial = "ROSEMIR DO ROCIO FERREIRA VOSS";

$std->tomador->endereco = new \stdClass();
$std->tomador->endereco->endereco = 'RUA JOAQUIM COSTA RIBEIRO';
$std->tomador->endereco->numero = '683';
$std->tomador->endereco->complemento = 'SEM COMP';
$std->tomador->endereco->bairro = 'BAIRRO ALTO';
$std->tomador->endereco->codigomunicipio = 4106902;
$std->tomador->endereco->uf = 'PR';
$std->tomador->endereco->cep = 82840190;

$std->servico = new \stdClass();
$std->servico->itemlistaservico = '1406';
//$std->servico->codigotributacaomunicipio = '522310000';
//$std->servico->codigocnae = '4520001';
$std->servico->discriminacao = 'REM E INST VIDRO R$ 190.33\r\r\nA FATURA AGRUPA OS VALORES DAS NF:39548/055 E 27548/RP\r\nTOTAL DA FATURA 27063 R$ 4.045,85 - COM A(S) PARCELA(S):\r\nPARC: 27063/0101 - VENC: 26/04/19 - VALOR: 4.045,85\r\r\nTRIB APROX R$: 17,61 FED / 9,52 MUN /\r\nDESCONTO SERVICOS     19,67-\r\nO.S./TIPO: 055428 / GM KM:  8803 FAB: 2018\r\nCHASSI: LYVUZ10CCKB214051 PLACA: BEN0995\r\nMOD/VER: 246       - XC60 2.0 DR\r\nCPF/RG: 16166869878 / 124311730\r\nEMISSAO: 27/03/19 - 11:29:14 IMPRESSAO: 27/03/19 - 11:29:35 [ OFFOS ]\r\nVENDEDOR: 12011 - LUIS HENRIQUE BACCINELLO\r\nCONCES: OPEN POINT DIST. VEICULOS LTDA DT. VENDA: 29/11/18\r\n.';
$std->servico->codigomunicipio = 4106902;

$std->servico->valores = new \stdClass();
$std->servico->valores->valorservicos = 190.33;
$std->servico->valores->valordeducoes = 0.00;
$std->servico->valores->valorpis = 0.00;
$std->servico->valores->valorcofins = 0.00;
$std->servico->valores->valorinss = 0.00;
$std->servico->valores->valorir = 0.00;
$std->servico->valores->valorcsll = 0.00;
//$std->servico->valores->issretido = 1;
$std->servico->valores->valoriss = 9.52;
$std->servico->valores->valorissretido = 0.00;
$std->servico->valores->outrasretencoes = 0.00;
$std->servico->valores->BaseCalculo = 190.33;
$std->servico->valores->aliquota = 0.05;
$std->servico->valores->valorliquidonfse = 190.33;
$std->servico->valores->descontoincondicionado = 0.00;
$std->servico->valores->descontocondicionado = 0.00;

//$std->Intermediarioservico = new \stdClass();
//$std->Intermediarioservico->RazaoSocial = 'INSCRICAO DE TESTE SIATU - D AGUA -PAULINO S'; 
//$std->Intermediarioservico->Cnpj = '99999999000191';
//$std->Intermediarioservico->InscricaoMunicipal = '8041700010';

//$std->construcaocivil = new \stdClass();
//$std->construcaocivil->codigoobra = '1234';
//$std->construcaocivil->art = '1234';

// Schema must be decoded before it can be used for validation
$jsonSchemaObject = json_decode($jsonSchema);
if (empty($jsonSchemaObject)) {
    echo "<h2>Erro de digitação no schema ! Revise</h2>";
    echo "<pre>";
    print_r($jsonSchema);
    echo "</pre>";
    die();
}
// The SchemaStorage can resolve references, loading additional schemas from file as needed, etc.
$schemaStorage = new SchemaStorage();
// This does two things:
// 1) Mutates $jsonSchemaObject to normalize the references (to file://mySchema#/definitions/integerData, etc)
// 2) Tells $schemaStorage that references to file://mySchema... should be resolved by looking in $jsonSchemaObject
$schemaStorage->addSchema('file://mySchema', $jsonSchemaObject);
// Provide $schemaStorage to the Validator so that references can be resolved during validation
$jsonValidator = new Validator(new Factory($schemaStorage));
// Do validation (use isValid() and getErrors() to check the result)
$jsonValidator->validate(
    $std,
    $jsonSchemaObject,
    Constraint::CHECK_MODE_COERCE_TYPES  //tenta converter o dado no tipo indicado no schema
);

if ($jsonValidator->isValid()) {
    echo "O JSON fornecido é validado no esquema.<br/>";
} else {
    echo "Dados não validados. Violações:<br/>";
    foreach ($jsonValidator->getErrors() as $error) {
        echo sprintf("[%s] %s<br/>", $error['property'], $error['message']);
    }
    die;
}
//salva se sucesso
// /home/u756270672/domains/fdc.procyon.com.br/public_html/wss/NFe/vendor/nfephp-org/sped-nfse-nacional/storage
$dirdest = "vendor/nfephp-org/sped-nfse-nacional/storage/jsonSchemes/v$version/";
mkdirs($dirdest);
file_put_contents($dirdest."rps.schema", $jsonSchema);


################################################################################
function buscaSchema($modelo, $versao) {
################################################################################
   $func = $modelo . '_' . $versao;
   return $func();
}

################################################################################
function abrasf_1_00() {
################################################################################
   return '{
       "title": "RPS",
       "type": "object",
       "properties": {
           "version": {
               "required": true,
               "type": "string"
           },
           "identificacaorps": {
               "required": true,
               "type": "object",
               "properties": {
                   "numero": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{1,15}"
                   },
                   "serie": {
                       "required": true,
                       "type": "string",
                       "maxLength": 5,
                       "pattern": "^[0-9A-Za-z]{1,5}$"
                   },
                   "tipo": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[1-3]{1}"
                   }
               }
           },
           "dataemissao": {
               "required": true,
               "type": "string",
               "pattern": "^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])T(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9])$"
           },
           "naturezaoperacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "regimeespecialtributacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "optantesimplesnacional": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "incentivadorcultural": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "status": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "tomador": {
               "required": true,
               "type": "object",
               "properties": {
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "endereco": {
                       "required": false,
                       "type": ["object","null"],
                       "properties": {
                           "endereco": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 125
                           },
                           "numero": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 10
                           },
                           "complemento": {
                               "required": false,
                               "type": ["string","null"],
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "bairro": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "codigomunicipio": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{7}"
                           },
                           "uf": {
                               "required": true,
                               "type": "string",
                               "maxLength": 2
                           },
                           "cep": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{8}"
                           }
                       }
                   }
               }
           },
           "servico": {
               "required": true,
               "type": "object",
               "properties": {
                   "itemlistaservico": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 5
                   },
                   "codigotributacaomunicipio": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 20
                   },
                   "discriminacao": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 2000
                   },
                   "codigomunicipio": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{7}"
                   },
                   "valores": {
                       "required": true,
                       "type": "object",
                       "properties": {
                           "valorservicos": {
                               "required": true,
                               "type": "number"
                           },
                           "valordeducoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorpis": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcofins": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorinss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorir": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcsll": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "issretido": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[1-2]{1}"
                           },
                           "valoriss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "outrasretencoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "aliquota": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontoincondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontocondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           }
                       }
                   }
               }
           },
           "intermediarioservico": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           },
           "construcaocivil": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "codigoobra": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "art": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           }
       }
   }';
}

################################################################################
function abrasf_2_01() {
################################################################################

   return '{
       "title": "RPS",
       "type": "object",
       "properties": {
           "version": {
               "required": true,
               "type": "string"
           },
           "identificacaorps": {
               "required": true,
               "type": "object",
               "properties": {
                   "numero": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{1,15}"
                   },
                   "serie": {
                       "required": true,
                       "type": "string",
                       "maxLength": 5,
                       "pattern": "^[0-9A-Za-z]{1,5}$"
                   },
                   "tipo": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[1-3]{1}"
                   }
               }
           },
           "dataemissao": {
               "required": true,
               "type": "string",
               "pattern": "^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])T(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9])$"
           },
           "naturezaoperacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "regimeespecialtributacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "optantesimplesnacional": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "incentivadorcultural": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "status": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "rpssubstituido": {
               "required": false,
               "type": ["object","null"],
               "numero": {
                    "required": true,
                    "type": "integer",
                    "pattern": "^[0-9]{1,15}"
               },
               "serie": {
                   "required": true,
                   "type": "string",
                   "maxLength": 5,
                   "pattern": "^[0-9A-Za-z]{1,5}$"
               },
               "tipo": {
                   "required": true,
                   "type": "integer",
                   "pattern": "^[1-3]{1}"
               }
           },
           "tomador": {
               "required": true,
               "type": "object",
               "properties": {
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "endereco": {
                       "required": false,
                       "type": ["object","null"],
                       "properties": {
                           "endereco": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 125
                           },
                           "numero": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 10
                           },
                           "complemento": {
                               "required": false,
                               "type": ["string","null"],
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "bairro": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "codigomunicipio": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{7}"
                           },
                           "uf": {
                               "required": true,
                               "type": "string",
                               "maxLength": 2
                           },
                           "cep": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{8}"
                           }
                       }
                   },
                   "contato": {
                       "required": false,
                       "type": ["object","null"],
                       "telefone": {
                           "required": false,
                           "type": "string"
                       },
                       "email": {
                           "required": false,
                           "type": "string"
                       }
                   }
               }
           },
           "servico": {
               "required": true,
               "type": "object",
               "properties": {
                   "itemlistaservico": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 5
                   },
                   "codigocnae": {
                       "required": false,
                       "type": "integer",
                       "pattern": "^[0-9]{7}"
                   },
                   "codigotributacaomunicipio": {
                       "required": false,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 20
                   },
                   "discriminacao": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 2000
                   },
                   "codigomunicipio": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{7}"
                   },
                   "valores": {
                       "required": true,
                       "type": "object",
                       "properties": {
                           "valorservicos": {
                               "required": true,
                               "type": "number"
                           },
                           "valordeducoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorpis": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcofins": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorinss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorir": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcsll": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "issretido": {
                               "required": false,
                               "type": "integer",
                               "pattern": "^[1-2]{1}"
                           },
                           "valoriss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "outrasretencoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "aliquota": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontoincondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontocondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           }
                       }
                   }
               }
           },
           "intermediarioservico": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           },
           "construcaocivil": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "codigoobra": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "art": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           }
       }
   }';
}

################################################################################
function abrasf_2_02() {
################################################################################

   return '{
       "title": "RPS",
       "type": "object",
       "properties": {
           "version": {
               "required": true,
               "type": "string"
           },
           "identificacaorps": {
               "required": true,
               "type": "object",
               "properties": {
                   "numero": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{1,15}"
                   },
                   "serie": {
                       "required": true,
                       "type": "string",
                       "maxLength": 5,
                       "pattern": "^[0-9A-Za-z]{1,5}$"
                   },
                   "tipo": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[1-3]{1}"
                   }
               }
           },
           "dataemissao": {
               "required": true,
               "type": "string",
               "pattern": "^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])T(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9])$"
           },
           "naturezaoperacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "regimeespecialtributacao": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-6]{1}"
           },
           "optantesimplesnacional": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "incentivadorcultural": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "status": {
               "required": true,
               "type": "integer",
               "pattern": "^[1-2]{1}"
           },
           "tomador": {
               "required": true,
               "type": "object",
               "properties": {
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "endereco": {
                       "required": false,
                       "type": ["object","null"],
                       "properties": {
                           "endereco": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 125
                           },
                           "numero": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 10
                           },
                           "complemento": {
                               "required": false,
                               "type": ["string","null"],
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "bairro": {
                               "required": true,
                               "type": "string",
                               "minLength": 1,
                               "maxLength": 60
                           },
                           "codigomunicipio": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{7}"
                           },
                           "uf": {
                               "required": true,
                               "type": "string",
                               "maxLength": 2
                           },
                           "cep": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[0-9]{8}"
                           }
                       }
                   }
               }
           },
           "servico": {
               "required": true,
               "type": "object",
               "properties": {
                   "itemlistaservico": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 5
                   },
                   "codigotributacaomunicipio": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 20
                   },
                   "discriminacao": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 2000
                   },
                   "codigomunicipio": {
                       "required": true,
                       "type": "integer",
                       "pattern": "^[0-9]{7}"
                   },
                   "valores": {
                       "required": true,
                       "type": "object",
                       "properties": {
                           "valorservicos": {
                               "required": true,
                               "type": "number"
                           },
                           "valordeducoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorpis": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcofins": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorinss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorir": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "valorcsll": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "issretido": {
                               "required": true,
                               "type": "integer",
                               "pattern": "^[1-2]{1}"
                           },
                           "valoriss": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "outrasretencoes": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "aliquota": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontoincondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           },
                           "descontocondicionado": {
                               "required": false,
                               "type": ["number", "null"]
                           }
                       }
                   }
               }
           },
           "intermediarioservico": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "razaosocial": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 115
                   },
                   "cnpj": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{14}"
                   },
                   "cpf": {
                       "required": false,
                       "type": ["string","null"],
                       "pattern": "^[0-9]{11}"
                   },
                   "inscricaomunicipal": {
                       "required": false,
                       "type": ["string","null"],
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           },
           "construcaocivil": {
               "required": false,
               "type": ["object","null"],
               "properties": {
                   "codigoobra": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   },
                   "art": {
                       "required": true,
                       "type": "string",
                       "minLength": 1,
                       "maxLength": 15
                   }
               }
           }
       }
   }';
}

############################################################################
function mkdirs($dir) {
############################################################################
   if (is_null($dir) || $dir === "") {
      return FALSE;
   }

   if (is_dir($dir) || $dir === "/") {
      return TRUE;
   }

   if (mkdirs(dirname($dir))) {
      $tmp = mkdir($dir);
      chmod($dir, 00777);
      return $tmp;
   }

   return FALSE;
 }

