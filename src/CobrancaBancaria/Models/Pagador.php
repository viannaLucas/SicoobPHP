<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class Pagador
{
    public ?string $nome = null;
    public ?string $numeroCpfCnpj = null;
    public ?string $email = null; // Added based on common fields
    public ?string $telefone = null; // Added based on common fields

    public static function createFromArray(array $data): self
    {
        $pagador = new self();
        $pagador->nome = $data['nome'] ?? null;
        $pagador->numeroCpfCnpj = $data['numeroCpfCnpj'] ?? $data['numeroCPFCNPJ'] ?? null; // Handle potential case variations
        $pagador->email = $data['email'] ?? null;
        $pagador->telefone = $data['telefone'] ?? null;
        // Add other fields if necessary, e.g., endereco, bairro, cidade, uf, cep
        return $pagador;
    }
}
