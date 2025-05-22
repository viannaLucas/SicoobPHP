<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class BeneficiarioFinalRequest
{
    public ?string $nome = null;
    public ?string $numeroCpfCnpj = null;

    public function __construct(?string $nome = null, ?string $numeroCpfCnpj = null)
    {
        $this->nome = $nome;
        $this->numeroCpfCnpj = $numeroCpfCnpj;
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->nome !== null) {
            $data['nome'] = $this->nome;
        }
        if ($this->numeroCpfCnpj !== null) {
            $data['numeroCpfCnpj'] = $this->numeroCpfCnpj;
        }
        return $data;
    }
}
