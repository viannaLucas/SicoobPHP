<?php

namespace viannaLucas\SicoobPhp\OAuth;

interface TokenPersistenceStrategy
{
    public function saveToken(Token $token): bool;
    public function loadToken(): ?Token;
}
