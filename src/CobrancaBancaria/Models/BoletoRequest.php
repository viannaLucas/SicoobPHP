<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

use viannaLucas\SicoobPhp\CobrancaBancaria\Models\PagadorRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BeneficiarioFinalRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\MensagemInstrucao;

class BoletoRequest
{
    public ?string $numeroContrato = null;
    public ?int $modalidade = null;
    public ?string $numeroContaCorrente = null;
    public ?string $especieDocumento = null;
    public ?string $dataEmissao = null; // Format: YYYY-MM-DDTHH:MM:SS-03:00
    public ?string $seuNumero = null;
    public ?string $identificacaoBoletoEmpresa = null;
    public ?string $codigoAceite = null; // 'A' para Aceite, 'N' para Não Aceite
    public float $valor; // Valor nominal do boleto
    public ?string $dataVencimento = null; // Format: YYYY-MM-DDTHH:MM:SS-03:00

    public ?PagadorRequest $pagador = null;
    public ?BeneficiarioFinalRequest $beneficiarioFinal = null;
    public ?MensagemInstrucao $mensagensInstrucao = null;
    
    public ?string $codigoTipoMulta = null; // 'VALORFIXO' ou 'PERCENTUAL'
    public ?string $dataMulta = null; // Format: YYYY-MM-DD
    public ?float $valorMulta = null;

    public ?string $codigoTipoJurosMora = null; // 'TAXAMENSAL' ou 'VALORDIA'
    public ?string $dataJurosMora = null; // Format: YYYY-MM-DD (data de início da cobrança)
    public ?float $valorJurosMora = null;

    public ?string $codigoTipoDesconto = null; // 'VALORFIXODATAINFORMADA', 'PERCENTUALDATAINFORMADA'
    public ?string $dataDesconto = null; // Format: YYYY-MM-DD
    public ?float $valorDesconto = null;
    
    // Campos adicionais que podem ser úteis
    public ?int $numeroParcela = null;
    public ?int $quantidadeDiasProtesto = null;
    public ?int $quantidadeDiasNegativacao = null;
    public ?string $gerarPdf = null; // "true" or "false"

    public function __construct(
        string $numeroContrato,
        int $modalidade,
        string $numeroContaCorrente,
        string $especieDocumento,
        string $dataEmissao,
        string $seuNumero,
        string $codigoAceite,
        float $valor,
        string $dataVencimento,
        PagadorRequest $pagador
    ) {
        $this->numeroContrato = $numeroContrato;
        $this->modalidade = $modalidade;
        $this->numeroContaCorrente = $numeroContaCorrente;
        $this->especieDocumento = $especieDocumento;
        $this->dataEmissao = $dataEmissao;
        $this->seuNumero = $seuNumero;
        $this->codigoAceite = $codigoAceite;
        $this->valor = $valor;
        $this->dataVencimento = $dataVencimento;
        $this->pagador = $pagador;
    }


    public function toArray(): array
    {
        $data = [
            'numeroContrato' => $this->numeroContrato,
            'modalidade' => $this->modalidade,
            'numeroContaCorrente' => $this->numeroContaCorrente,
            'especieDocumento' => $this->especieDocumento,
            'dataEmissao' => $this->dataEmissao,
            'seuNumero' => $this->seuNumero,
            'identificacaoBoletoEmpresa' => $this->identificacaoBoletoEmpresa,
            'codigoAceite' => $this->codigoAceite,
            'valor' => $this->valor,
            'dataVencimento' => $this->dataVencimento,
            'pagador' => $this->pagador ? $this->pagador->toArray() : null,
            'beneficiarioFinal' => $this->beneficiarioFinal ? $this->beneficiarioFinal->toArray() : null,
            'mensagensInstrucao' => $this->mensagensInstrucao ? $this->mensagensInstrucao->toArray() : null,
            'codigoTipoMulta' => $this->codigoTipoMulta,
            'dataMulta' => $this->dataMulta,
            'valorMulta' => $this->valorMulta,
            'codigoTipoJurosMora' => $this->codigoTipoJurosMora,
            'dataJurosMora' => $this->dataJurosMora,
            'valorJurosMora' => $this->valorJurosMora,
            'codigoTipoDesconto' => $this->codigoTipoDesconto,
            'dataDesconto' => $this->dataDesconto,
            'valorDesconto' => $this->valorDesconto,
            'numeroParcela' => $this->numeroParcela,
            'quantidadeDiasProtesto' => $this->quantidadeDiasProtesto,
            'quantidadeDiasNegativacao' => $this->quantidadeDiasNegativacao,
            'gerarPdf' => $this->gerarPdf,
        ];
        // Remove null values to keep payload clean, but allow 'identificacaoBoletoEmpresa' to be empty string if set
        return array_filter($data, fn ($value) => $value !== null || $value === '');
    }
}
