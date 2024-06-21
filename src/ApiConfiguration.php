<?php

namespace viannaLucas\SicoobPhp;

class ApiConfiguration
{
    public const BASE_URL = 'https://api.sicoob.com.br/';
    public const AUTH_URL = 'https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token';
    
    /**
     * 
     * @param string $clientId
     * @param string $clientSecret
     * @param array<\viannaLucas\SicoobPhp\OAuth\Scope> $scopes
     * @param string $fileCertPath
     * @param string $fileCertKey
     */
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected array $scopes,
        protected string $fileCertPath,
        protected string $fileCertKey,
    ) {

    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
    
    /**
     * 
     * @return array<\viannaLucas\SicoobPhp\OAuth\Scope>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getFileCertPath(): string
    {
        return $this->fileCertPath;
    }

    public function getFileCertKey(): string
    {
        return $this->fileCertKey;
    }
}
