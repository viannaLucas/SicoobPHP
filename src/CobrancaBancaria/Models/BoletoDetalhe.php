<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

use viannaLucas\SicoobPhp\CobrancaBancaria\Models\Pagador;

class BoletoDetalhe
{
    public ?string $numeroContrato = null;
    public ?string $modalidade = null;
    public ?string $numeroContaCorrente = null;
    public ?string $especieDocumento = null;
    public ?string $dataEmissao = null;
    public ?string $nossoNumero = null;
    public ?string $seuNumero = null;
    public ?string $identificacaoBoletoEmpresa = null;
    public ?string $codigoBarras = null;
    public ?string $linhaDigitavel = null;
    public ?string $valor = null; // Using string for currency to avoid float issues
    public ?string $dataVencimento = null;
    public ?string $dataLimitePagamento = null;
    public ?string $valorAbatimento = null; // Using string
    public ?string $tipoDesconto1 = null;
    public ?string $dataDesconto1 = null;
    public ?string $valorDesconto1 = null; // Using string
    public ?string $tipoDesconto2 = null;
    public ?string $dataDesconto2 = null;
    public ?string $valorDesconto2 = null; // Using string
    public ?string $tipoDesconto3 = null;
    public ?string $dataDesconto3 = null;
    public ?string $valorDesconto3 = null; // Using string
    public ?string $tipoMulta = null;
    public ?string $dataMulta = null;
    public ?string $valorMulta = null; // Using string
    public ?string $tipoJurosMora = null;
    public ?string $dataJurosMora = null;
    public ?string $valorJurosMora = null; // Using string
    public ?string $numeroParcela = null;
    public ?string $aceite = null;
    public ?string $codigoNegativacao = null;
    public ?int $prazoNegativacao = null;
    public ?string $codigoProtesto = null;
    public ?int $prazoProtesto = null;
    public ?Pagador $pagador = null;
    public ?Pagador $sacadorAvalista = null;
    public ?string $situacaoBoleto = null;
    public ?string $especieMoeda = null; // e.g., "REAL"
    public ?string $codigoMoeda = null; // e.g., "09"

    public static function createFromJson(string $json): self
    {
        $data = json_decode($json, true);
        // Assuming the actual boleto data is under a "resultado" key, as is common in this API
        $boletoData = $data['resultado'] ?? $data; 

        $boletoDetalhe = new self();
        $boletoDetalhe->numeroContrato = $boletoData['numeroContrato'] ?? null;
        $boletoDetalhe->modalidade = $boletoData['modalidade'] ?? null;
        $boletoDetalhe->numeroContaCorrente = $boletoData['numeroContaCorrente'] ?? null;
        $boletoDetalhe->especieDocumento = $boletoData['especieDocumento'] ?? null;
        $boletoDetalhe->dataEmissao = $boletoData['dataEmissao'] ?? null;
        $boletoDetalhe->nossoNumero = $boletoData['nossoNumero'] ?? null;
        $boletoDetalhe->seuNumero = $boletoData['seuNumero'] ?? null;
        $boletoDetalhe->identificacaoBoletoEmpresa = $boletoData['identificacaoBoletoEmpresa'] ?? null;
        $boletoDetalhe->codigoBarras = $boletoData['codigoBarras'] ?? null;
        $boletoDetalhe->linhaDigitavel = $boletoData['linhaDigitavel'] ?? null;
        $boletoDetalhe->valor = $boletoData['valor'] ?? null;
        $boletoDetalhe->dataVencimento = $boletoData['dataVencimento'] ?? null;
        $boletoDetalhe->dataLimitePagamento = $boletoData['dataLimitePagamento'] ?? null;
        $boletoDetalhe->valorAbatimento = $boletoData['valorAbatimento'] ?? null;
        $boletoDetalhe->tipoDesconto1 = $boletoData['tipoDesconto1'] ?? null;
        $boletoDetalhe->dataDesconto1 = $boletoData['dataDesconto1'] ?? null;
        $boletoDetalhe->valorDesconto1 = $boletoData['valorDesconto1'] ?? null;
        $boletoDetalhe->tipoDesconto2 = $boletoData['tipoDesconto2'] ?? null;
        $boletoDetalhe->dataDesconto2 = $boletoData['dataDesconto2'] ?? null;
        $boletoDetalhe->valorDesconto2 = $boletoData['valorDesconto2'] ?? null;
        $boletoDetalhe->tipoDesconto3 = $boletoData['tipoDesconto3'] ?? null;
        $boletoDetalhe->dataDesconto3 = $boletoData['dataDesconto3'] ?? null;
        $boletoDetalhe->valorDesconto3 = $boletoData['valorDesconto3'] ?? null;
        $boletoDetalhe->tipoMulta = $boletoData['tipoMulta'] ?? null;
        $boletoDetalhe->dataMulta = $boletoData['dataMulta'] ?? null;
        $boletoDetalhe->valorMulta = $boletoData['valorMulta'] ?? null;
        $boletoDetalhe->tipoJurosMora = $boletoData['tipoJurosMora'] ?? null;
        $boletoDetalhe->dataJurosMora = $boletoData['dataJurosMora'] ?? null;
        $boletoDetalhe->valorJurosMora = $boletoData['valorJurosMora'] ?? null;
        $boletoDetalhe->numeroParcela = $boletoData['numeroParcela'] ?? null;
        $boletoDetalhe->aceite = $boletoData['aceite'] ?? null;
        $boletoDetalhe->codigoNegativacao = $boletoData['codigoNegativacao'] ?? null;
        $boletoDetalhe->prazoNegativacao = $boletoData['prazoNegativacao'] ?? null;
        $boletoDetalhe->codigoProtesto = $boletoData['codigoProtesto'] ?? null;
        $boletoDetalhe->prazoProtesto = $boletoData['prazoProtesto'] ?? null;
        $boletoDetalhe->situacaoBoleto = $boletoData['situacaoBoleto'] ?? null;
        $boletoDetalhe->especieMoeda = $boletoData['especieMoeda'] ?? null;
        $boletoDetalhe->codigoMoeda = $boletoData['codigoMoeda'] ?? null;

        if (isset($boletoData['pagador']) && is_array($boletoData['pagador'])) {
            $boletoDetalhe->pagador = Pagador::createFromArray($boletoData['pagador']);
        }

        if (isset($boletoData['sacadorAvalista']) && is_array($boletoData['sacadorAvalista'])) {
            $boletoDetalhe->sacadorAvalista = Pagador::createFromArray($boletoData['sacadorAvalista']);
        }
        
        return $boletoDetalhe;
    }
}
