<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria;

use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoDetalhe;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\Pagador;

class BoletoResponse
{
    /** @var array<BoletoDetalhe> */
    public array $boletos = [];
    public ?int $totalPaginas = null;
    public ?int $totalRegistros = null;

    public static function createFromJson(string $json): self
    {
        $data = json_decode($json, true);
        $response = new self();

        if (isset($data['resultado']) && is_array($data['resultado'])) {
            foreach ($data['resultado'] as $boletoData) {
                $boletoDetalhe = new BoletoDetalhe();
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
                    $pagador = new Pagador();
                    $pagador->nome = $boletoData['pagador']['nome'] ?? null;
                    $pagador->numeroCpfCnpj = $boletoData['pagador']['numeroCpfCnpj'] ?? null;
                    $pagador->email = $boletoData['pagador']['email'] ?? null;
                    $pagador->telefone = $boletoData['pagador']['telefone'] ?? null;
                    // Assuming other pagador fields like endereco, bairro, cidade, uf, cep if they exist
                    $boletoDetalhe->pagador = $pagador;
                }

                if (isset($boletoData['sacadorAvalista']) && is_array($boletoData['sacadorAvalista'])) {
                    $sacadorAvalista = new Pagador(); // Reusing Pagador DTO for sacadorAvalista
                    $sacadorAvalista->nome = $boletoData['sacadorAvalista']['nome'] ?? null;
                    $sacadorAvalista->numeroCpfCnpj = $boletoData['sacadorAvalista']['numeroCpfCnpj'] ?? null;
                    $boletoDetalhe->sacadorAvalista = $sacadorAvalista;
                }
                
                $response->boletos[] = $boletoDetalhe;
            }
        }

        if (isset($data['paginacao']) && is_array($data['paginacao'])) {
            $response->totalPaginas = $data['paginacao']['quantidadePaginas'] ?? $data['paginacao']['totalPaginas'] ?? null; // Adjusted for potential variations
            $response->totalRegistros = $data['paginacao']['quantidadeRegistros'] ?? $data['paginacao']['totalRegistros'] ?? null; // Adjusted for potential variations
        }

        return $response;
    }
}
