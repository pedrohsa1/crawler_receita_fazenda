<?php

require "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Cookie\CookieJar;
use Sunra\PhpSimple\HtmlDomParser;
use TwoCaptcha\TwoCaptcha;

//Configurando Client
$client = new Client([
    'curl' => array( CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false ),
    'allow_redirects' => true,
    'cookies'         => true,
    'verify'          => false,
    "headers" => [
        "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
    ]
]);


//Obtendo a página inicial
$htmlPagInicial = $client->request("GET",'http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao')->getBody();
$dom = HtmlDomParser::str_get_html($htmlPagInicial);

echo $htmlPagInicial;

$requestVerificationToken = $dom->find("input[name=__RequestVerificationToken]",0)->getAttribute('value');
$cnpj = "37.000.373/0001-72";

echo "\n\n";
echo "ViewState requestVerificationToken: \n";
echo $requestVerificationToken;
echo "\n\n";

//Resolvendo recaptcha no 2captcha.com
$solver = new TwoCaptcha("b991893b4578a5ff5badb02189c4991b");
try {
    echo "\n\nResolveno recaptcha... \n\n";
    $result = $solver->recaptcha([
        'sitekey' => "6LcEMN0UAAAAALbtISbFpm_VTni8JeVePDUjmOP4",
        'url'     => 'http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao',
    ]);
} catch (\Exception $e) {
    echo $e->getMessage();
}


echo "Token Recaptcha Resolvido: \n";
echo $result->code;

try {
    echo "\n\nEnviando CNPJ...\n\n";
    $htmlInfoCnjp = $client->request("POST", "http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao/Continuar",
    [
        'form_params' => [
            '__RequestVerificationToken' =>  $requestVerificationToken,
            'cnpj'   =>  $cnpj,
            'identificacaoToken' => $result->code
        ],
        'headers' => [
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Host' => 'www8.receita.fazenda.gov.br',
            'Origin' => 'http://www8.receita.fazenda.gov.br',
            'Referer' => 'http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36'
        ],
        'debug' => true
    ])->getBody();
}
catch (ClientException $e) {
    $response = $e->getResponse();
    $responseBodyAsString = $response->getBody()->getContents();
    echo "Erro: " . $response;
}

echo "Pagina Info CNPJ: \n". $htmlInfoCnjp;

?>