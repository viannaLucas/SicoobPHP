<?php

namespace viannaLucas\SicoobPhp\ContaCorrente;

class TransacaoExtrato
{

    public function __construct(
        protected TipoTransacaoExtrato $tipo,
        protected float $valor,
        protected \DateTime $data,
        protected \DateTime $dataLote,
        protected string $descricao,
        protected string $numeroDocumento,
        protected ?string $cpfCnpj,
        protected ?string $descInfComplementar
    )
    {
        
    }

    public function getTipo(): TipoTransacaoExtrato
    {
        return $this->tipo;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getData(): \DateTime
    {
        return $this->data;
    }

    public function getDataLote(): \DateTime
    {
        return $this->dataLote;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getNumeroDocumento(): string
    {
        return $this->numeroDocumento;
    }

    public function getCpfCnpj(): ?string
    {
        return $this->cpfCnpj;
    }

    public function getDescInfComplementar(): ?string
    {
        return $this->descInfComplementar;
    }

    public function setTipo(TipoTransacaoExtrato $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function setValor(float $valor): void
    {
        $this->valor = $valor;
    }

    public function setData(\DateTime $data): void
    {
        $this->data = $data;
    }

    public function setDataLote(\DateTime $dataLote): void
    {
        $this->dataLote = $dataLote;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function setNumeroDocumento(string $numeroDocumento): void
    {
        $this->numeroDocumento = $numeroDocumento;
    }

    public function setCpfCnpj(?string $cpfCnpj): void
    {
        $this->cpfCnpj = $cpfCnpj;
    }

    public function setDescInfComplementar(?string $descInfComplementar): void
    {
        $this->descInfComplementar = $descInfComplementar;
    }
    
    static function createFromJson(string $json): TransacaoExtrato{
        $obj = json_decode($json);
        return new TransacaoExtrato(
            TipoTransacaoExtrato::from($obj->tipo), 
            $obj->valor, 
            new \DateTime($obj->data), 
            new \DateTime($obj->dataLote), 
            $obj->descricao, 
            $obj->numeroDocumento, 
            isset($obj->cpfCnpj) ? $obj->cpfCnpj : null, 
            isset($obj->descInfComplementar) ? $obj->descInfComplementar : null, 
        );
    }
}