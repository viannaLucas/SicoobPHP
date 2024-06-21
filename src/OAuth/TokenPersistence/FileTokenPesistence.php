<?php

namespace viannaLucas\SicoobPhp\OAuth\TokenPersistence;

use viannaLucas\SicoobPhp\OAuth\Token;
use viannaLucas\SicoobPhp\OAuth\TokenPersistenceStrategy;

class FileTokenPesistence implements TokenPersistenceStrategy
{
    public function __construct(
        protected string $fullPathFile
    ) {

    }

    public function loadToken(): ?Token
    {
        if(!is_file($this->fullPathFile) && !is_readable($this->fullPathFile)) {
            return null;
        }
        $tokenUnserialized = unserialize((string)file_get_contents($this->fullPathFile));
        if($tokenUnserialized === false){
            return null;
        }
        return $tokenUnserialized;
    }

    public function saveToken(Token $token): bool
    {
        return file_put_contents($this->fullPathFile, serialize($token)) !== false;
    }
}
