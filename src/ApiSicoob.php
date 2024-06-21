<?php

namespace viannaLucas\SicoobPhp;

use CurlHandle;
use Exception;
use viannaLucas\SicoobPhp\ApiConfiguration;
use viannaLucas\SicoobPhp\OAuth\OAuth;
use viannaLucas\SicoobPhp\OAuth\TokenPersistenceStrategy;
use viannaLucas\SicoobPhp\Response;

class ApiSicoob
{
    private OAuth $oAuth;

    public function __construct(
        protected ApiConfiguration $apiConfiguration,
        protected ?TokenPersistenceStrategy $tokenPersistence,
    ) {
        $this->oAuth = new OAuth($apiConfiguration, $tokenPersistence);
    }


    protected function createCurl(): CurlHandle
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->apiConfiguration->getFileCertPath());
        curl_setopt($curl, CURLOPT_SSLKEY, $this->apiConfiguration->getFileCertKey());
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        return $curl;
    }
    
    /**
     * 
     * @param string $url
     * @param array<string> $params
     * @return Response
     * @throws Exception
     */
    public function getRequest(string $url, array $params): Response
    {
        $httpParams = [
            'Authorization: Bearer ' . $this->oAuth->getToken()->getAccessToken(),
            'accept: application/json',
        ];

        $fullUrl = ApiConfiguration::BASE_URL . $url. '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $curl = $this->createCurl();
        curl_setopt($curl, CURLOPT_URL, $fullUrl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpParams);

        $curlReply = (string) curl_exec($curl);
        $curlError = curl_error($curl);
        if ($curlError !== '') {
            throw new Exception("Curl error: " . $curlError);
        }
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return new Response($httpCode, $curlReply);
    }
    
    protected function validateResponse(Response $response): object
    {
        if ($response->getHttpCode() != 200) {
            throw new Exception('Error request. Http Error Code: ' . $response->getHttpCode());
        }
        $objResp = json_decode($response->getBody());
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid response json format');
        }
        return $objResp;
    }
}
