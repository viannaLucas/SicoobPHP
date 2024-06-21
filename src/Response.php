<?php

namespace viannaLucas\SicoobPhp;

class Response
{
    public function __construct(
        protected int $httpCode,
        protected string $body,
    ) {

    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
