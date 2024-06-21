<?php

namespace viannaLucas\SicoobPhp\ContaCorrente;

class Extrato
{

    public function __construct(
        protected float $saldoAtual,
        protected float $saldoBloqueado,
        protected float $saldoLimite,
        protected float $saldoAnterior,
        protected float $saldoJudicial,
        protected float $saldoJudicialAnterior,
        /** @var array<\viannaLucas\Sicoob\Extrato\TransacaoExtrato> * */
        protected array $transacoes,
    )
    {
        
    }

    public function getSaldoAtual(): float
    {
        return $this->saldoAtual;
    }

    public function getSaldoBloqueado(): float
    {
        return $this->saldoBloqueado;
    }

    public function getSaldoLimite(): float
    {
        return $this->saldoLimite;
    }

    public function getSaldoAnterior(): float
    {
        return $this->saldoAnterior;
    }

    public function getSaldoJudicial(): float
    {
        return $this->saldoJudicial;
    }

    public function getSaldoJudicialAnterior(): float
    {
        return $this->saldoJudicialAnterior;
    }

    public function getTransacoes(): array
    {
        return $this->transacoes;
    }

    public function setSaldoAtual(float $saldoAtual): void
    {
        $this->saldoAtual = $saldoAtual;
    }

    public function setSaldoBloqueado(float $saldoBloqueado): void
    {
        $this->saldoBloqueado = $saldoBloqueado;
    }

    public function setSaldoLimite(float $saldoLimite): void
    {
        $this->saldoLimite = $saldoLimite;
    }

    public function setSaldoAnterior(float $saldoAnterior): void
    {
        $this->saldoAnterior = $saldoAnterior;
    }

    public function setSaldoJudicial(float $saldoJudicial): void
    {
        $this->saldoJudicial = $saldoJudicial;
    }

    public function setSaldoJudicialAnterior(float $saldoJudicialAnterior): void
    {
        $this->saldoJudicialAnterior = $saldoJudicialAnterior;
    }

    public function setTransacoes(array $transacoes): void
    {
        $this->transacoes = $transacoes;
    }
    
    static function createFromJson(string $json): Extrato{
        $obj = json_decode($json);
        $transacoes = [];
        foreach($obj->resultado->transacoes as $t){
            $transacoes[] = TransacaoExtrato::createFromJson(json_encode($t));
        }
        return new Extrato(
            $obj->resultado->saldoAtual, 
            $obj->resultado->saldoBloqueado, 
            $obj->resultado->saldoLimite, 
            $obj->resultado->saldoAnterior, 
            isset($obj->resultado->saldoJudicial) ? $obj->resultado->saldoJudicial: 0, 
            isset($obj->resultado->saldoJudicialAnterior) ? $obj->resultado->saldoJudicialAnterior: 0, 
            $transacoes);
    }
}