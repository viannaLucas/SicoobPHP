<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class PagadorRequest
{
    public ?string $nome = null;
    public ?string $numeroCpfCnpj = null;
    public ?string $endereco = null;
    public ?string $bairro = null;
    public ?string $cidade = null;
    public ?string $cep = null;
    public ?string $uf = null;
    public ?string $email = null;
    // Adicionando campos que podem ser úteis e são comuns
    public ?string $logradouro = null;
    public ?string $complemento = null;
    public ?string $numero = null;


    public function __construct(
        ?string $nome = null,
        ?string $numeroCpfCnpj = null,
        ?string $logradouro = null, // Changed from endereco to logradouro for clarity
        ?string $numero = null,
        ?string $bairro = null,
        ?string $cidade = null,
        ?string $cep = null,
        ?string $uf = null,
        ?string $email = null,
        ?string $complemento = null
    ) {
        $this->nome = $nome;
        $this->numeroCpfCnpj = $numeroCpfCnpj;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->cep = $cep;
        $this->uf = $uf;
        $this->email = $email;
        $this->complemento = $complemento;
        $this->endereco = trim(sprintf('%s, %s %s', $this->logradouro, $this->numero, $this->complemento), " ,");
    }


    public function toArray(): array
    {
        $data = [
            'nome' => $this->nome,
            'numeroCpfCnpj' => $this->numeroCpfCnpj,
            'endereco' => $this->endereco, // Maintained for compatibility if API expects 'endereco'
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'cep' => $this->cep,
            'uf' => $this->uf,
            'email' => $this->email,
            'complemento' => $this->complemento,
        ];
        // Remove null values to keep payload clean
        return array_filter($data, fn ($value) => $value !== null);
    }
}
