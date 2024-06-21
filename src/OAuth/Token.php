<?php

namespace viannaLucas\SicoobPhp\OAuth;

use DateTime;

class Token
{

    /**
     * 
     * @param string $accessToken
     * @param string $tokenType
     * @param int $expiresIn
     * @param string $scope
     */
    public function __construct(
        protected string $accessToken,
        protected string $tokenType,
        protected int $expiresIn,
        protected string $scope,
    )
    {
        
    }

    public function isExpired(): bool
    {
        return (new DateTime())->getTimestamp() >= $this->expiresIn;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function setTokenType(string $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    public function __serialize(): array
    {
        $data = [];
        $data['accessToken'] = $this->accessToken;
        $data['expiresIn'] = $this->expiresIn;
        $data['scope'] = $this->scope;
        $data['tokenType'] = $this->tokenType;
        return $data;
    }
    
    /**
     * 
     * @param array<string> $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->accessToken = $data['accessToken'];
        $this->expiresIn = intval($data['expiresIn']);
        $this->scope = $data['scope'];
        $this->tokenType = $data['tokenType'];
    }
}