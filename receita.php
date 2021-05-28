<?php

require "vendor/autoload.php";

use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;
use TwoCaptcha\TwoCaptcha;

$client = new Client([
    'curl' => array( CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false ),
    'allow_redirects' => false,
    'cookies'         => true,
    'verify'          => false,
    "headers" => [
        "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36",        
    ],
]);

$URL = "http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao";

$htmlPagInicial = $client->request("GET", $URL)->getBody();
$dom = HtmlDomParser::str_get_html($htmlPagInicial);

$requestVerificationToken = $dom->find("input[name=__RequestVerificationToken]",0)->getAttribute('value');
$idFormSK = $dom->getElementById("identificacao")->getAttribute('data-sk');
$cnpj = "37.000.373/0001-72";
$sitekey = "6LcEMN0UAAAAALbtISbFpm_VTni8JeVePDUjmOP4";

$solver = new TwoCaptcha("b991893b4578a5ff5badb02189c4991b");

try {
    echo "\n\nResolveno recaptcha: \n\n";
    $result = $solver->recaptcha([
        'sitekey' => $sitekey,
        'url'     => 'http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao',
    ]);
} catch (\Exception $e) {
    echo $e->getMessage();
}

$tokenRecaptcha2 = $result->code;

try {
$htmlInfoCnjp = $client->request("POST", "http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao/Continuar",
[
    'form_params' => [
        '__RequestVerificationToken' =>  $requestVerificationToken,
        'cnpj'   =>  $cnpj,
        'identificacaoToken' => $tokenRecaptcha2
    ],
])->getBody();
}
catch (GuzzleHttp\Exception\ClientException $e) {
    $response = $e->getResponse();
    $responseBodyAsString = $response->getBody()->getContents();
    echo $response;
}

echo $htmlInfoCnjp;

?>