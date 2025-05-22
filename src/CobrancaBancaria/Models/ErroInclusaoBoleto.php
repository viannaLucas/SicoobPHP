<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class ErroInclusaoBoleto
{
    public ?string $codigo = null;
    public ?string $mensagem = null;
    public ?string $detalhe = null; // For additional error details

    public function __construct(?string $codigo = null, ?string $mensagem = null, ?string $detalhe = null)
    {
        $this->codigo = $codigo;
        $this->mensagem = $mensagem;
        $this->detalhe = $detalhe;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['codigo'] ?? $data['status'] ?? null, // 'status' field from Postman example for individual error
            $data['mensagem'] ?? $data['message'] ?? $data['msg'] ?? null, // Common variations for message
            $data['detalhe'] ?? $data['detail'] ?? null
        );
    }
}
