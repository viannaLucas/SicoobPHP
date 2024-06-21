<?php

namespace viannaLucas\SicoobPhp\OAuth\TokenPersistence;

use Exception;
use viannaLucas\SicoobPhp\OAuth\Token;
use viannaLucas\SicoobPhp\OAuth\TokenPersistenceStrategy;

class EncryptFileTokenPesistence implements TokenPersistenceStrategy
{
    private string $method = "AES-256-CBC";

    public function __construct(
        protected string $fullPathFile,
        protected string $secretKey
    ) {

    }

    public function loadToken(): ?Token
    {
        if (!is_file($this->fullPathFile) && !is_readable($this->fullPathFile)) {
            return null;
        }
        $tokenUnserialized = unserialize($this->decrypt((string)file_get_contents($this->fullPathFile)));
        if($tokenUnserialized === false){
            return null;
        }
        return $tokenUnserialized;
    }

    public function saveToken(Token $token): bool
    {
        $encryptedContent = $this->encrypt(serialize($token));
        return file_put_contents($this->fullPathFile, $encryptedContent) !== false;
    }

    protected function encrypt(string $contentUncrypted): string
    {
        $initVector = openssl_random_pseudo_bytes((int)openssl_cipher_iv_length($this->method));
        $encrypted = openssl_encrypt($contentUncrypted, $this->method, $this->secretKey, 0, $initVector);
        return base64_encode($initVector.$encrypted);
    }

    protected function decrypt(string $contentEncrypted): string
    {
        $encrypted = base64_decode($contentEncrypted);
        $initVector = substr($encrypted, 0, (int) openssl_cipher_iv_length($this->method));
        $encrypted = substr($encrypted, (int) openssl_cipher_iv_length($this->method));
        $decrypted = openssl_decrypt($encrypted, $this->method, $this->secretKey, 0, $initVector);
        if($decrypted === false){
            throw new Exception('Error in Decrypt process');
        }
        return $decrypted;
    }
}
