<?php

namespace viannaLucas\SicoobPhp\OAuth;

use CurlHandle;
use DateInterval;
use DateTime;
use Exception;
use viannaLucas\SicoobPhp\ApiConfiguration;
use viannaLucas\SicoobPhp\OAuth\Scope;
use viannaLucas\SicoobPhp\OAuth\Token;

class OAuth
{
    protected Token $token;

    public function __construct(
        protected ApiConfiguration $apiConfiguration,
        protected ?TokenPersistenceStrategy $tokenPersistence,
    ) {

    }

    protected function setToken(Token $token): void
    {
        $this->token = $token;

        if (isset($this->tokenPersistence)) {
            $this->tokenPersistence->saveToken($token);
        }
    }

    public function getToken(): Token
    {
        if (isset($this->token) && !$this->token->isExpired()) {
            return $this->token;
        }
        if (isset($this->tokenPersistence)) {
            $tokenLoaded = $this->tokenPersistence->loadToken();
            if ($tokenLoaded != null && !$tokenLoaded->isExpired()) {
                return $tokenLoaded;
            }
        }
        return $this->requestToken();
    }
    
    protected function createCurlConectiontion(): CurlHandle
    {
        $param = [
            'client_id' => $this->apiConfiguration->getClientId(),
            'client_secret' => $this->apiConfiguration->getClientSecret(),
            'scope' => Scope::convertToString($this->apiConfiguration->getScopes()),
            'grant_type' => 'client_credentials'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => ApiConfiguration::AUTH_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYHOST => 2,
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => http_build_query($param, '', '&', PHP_QUERY_RFC3986),
          CURLOPT_SSLCERT => $this->apiConfiguration->getFileCertPath(),
          CURLOPT_SSLKEY => $this->apiConfiguration->getFileCertKey(),
          CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));

        return $curl;
    }

    protected function requestToken(): ?Token
    {
        $curl = $this->createCurlConectiontion();
        $serverResponse = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error !== '') {
            throw new Exception($error . ' Error Number: ' . curl_errno($curl));
        }
        if ($serverResponse == '') {
            throw new Exception("Empty response, probably the call limit has been reached...\n");
        }
        $obj = json_decode((string)$serverResponse);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid Json');
        }
        $expireIn = intval($obj->expires_in) - 30; //remove 30 secounds for security leg comunications
        $dateExpires = (new DateTime())->add(new DateInterval('PT' . $expireIn . 'S'));
        $this->setToken(new Token($obj->access_token, $obj->token_type, $dateExpires->getTimestamp(), $obj->scope));
        return $this->getToken();
    }

    public function getApiConfiguration(): ApiConfiguration
    {
        return $this->apiConfiguration;
    }

    public function getTokenPersistence(): ?TokenPersistenceStrategy
    {
        return $this->tokenPersistence;
    }

}
