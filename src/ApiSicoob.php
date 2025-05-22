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
    
    /**
     * 
     * @param string $url
     * @param array<mixed> $data The data to be sent in the request body as JSON
     * @return Response
     * @throws Exception
     */
    public function postRequest(string $url, array $data): Response
    {
        $httpHeaders = [
            'Authorization: Bearer ' . $this->oAuth->getToken()->getAccessToken(),
            'Content-Type: application/json',
            'accept: application/json', // Assuming API accepts and responds with JSON
        ];

        // The BASE_URL should not be prepended here if it's already part of the $url in ApiCobrancaBancaria
        // However, if $url is a relative path, it should be prepended.
        // For now, let's assume $url will be the full path or relative to a base already handled by the caller.
        // If $url is like "/boletos", then ApiConfiguration::BASE_URL_COBRANCA_BANCARIA should be prepended by the caller.

        $curl = $this->createCurl();
        curl_setopt($curl, CURLOPT_URL, $url); // Use $url directly
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);

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
        // Adjusted to allow 200, 201 (Created), 202 (Accepted) for successful POST/PUT etc.
        if ($response->getHttpCode() < 200 || $response->getHttpCode() >= 300) {
            throw new Exception('Error request. Http Error Code: ' . $response->getHttpCode() . ' Body: ' . $response->getBody());
        }
        $objResp = json_decode($response->getBody());
        if (json_last_error() !== JSON_ERROR_NONE && !empty(trim($response->getBody()))) { // Check if body is not empty before erroring on JSON
            throw new Exception('Invalid response json format');
        }
        return $objResp ?? new \stdClass(); // Return empty stdClass if body was empty
    }
}
