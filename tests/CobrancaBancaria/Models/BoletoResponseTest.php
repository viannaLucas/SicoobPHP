<?php

namespace viannaLucas\SicoobPhp\Tests\CobrancaBancaria\Models;

use PHPUnit\Framework\TestCase;
use viannaLucas\SicoobPhp\CobrancaBancaria\BoletoResponse;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoDetalhe;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\Pagador;

class BoletoResponseTest extends TestCase
{
    public function testDeveCriarAPartirDeJsonValidoParaListaDeBoletos()
    {
        $json = '{
            "resultado": [
                {
                    "numeroContrato": "123456",
                    "nossoNumero": "0011223344",
                    "seuNumero": "SN001",
                    "dataEmissao": "2023-10-01",
                    "dataVencimento": "2023-10-30",
                    "valor": "150.75",
                    "situacaoBoleto": "Em Aberto",
                    "pagador": {
                        "nome": "Pagador Teste Um",
                        "numeroCpfCnpj": "11122233344"
                    }
                },
                {
                    "numeroContrato": "123457",
                    "nossoNumero": "0055667788",
                    "seuNumero": "SN002",
                    "dataEmissao": "2023-10-02",
                    "dataVencimento": "2023-11-15",
                    "valor": "250.00",
                    "situacaoBoleto": "Baixado",
                     "pagador": {
                        "nome": "Pagador Teste Dois",
                        "numeroCpfCnpj": "55566677788"
                    }
                }
            ],
            "paginacao": {
                "quantidadePaginas": 5,
                "quantidadeRegistros": 50
            }
        }';

        $boletoResponse = BoletoResponse::createFromJson($json);

        $this->assertInstanceOf(BoletoResponse::class, $boletoResponse);
        $this->assertCount(2, $boletoResponse->boletos);
        $this->assertEquals(5, $boletoResponse->totalPaginas);
        $this->assertEquals(50, $boletoResponse->totalRegistros);

        $primeiroBoleto = $boletoResponse->boletos[0];
        $this->assertInstanceOf(BoletoDetalhe::class, $primeiroBoleto);
        $this->assertEquals("123456", $primeiroBoleto->numeroContrato);
        $this->assertEquals("0011223344", $primeiroBoleto->nossoNumero);
        $this->assertEquals("SN001", $primeiroBoleto->seuNumero);
        $this->assertEquals("150.75", $primeiroBoleto->valor);
        $this->assertInstanceOf(Pagador::class, $primeiroBoleto->pagador);
        $this->assertEquals("Pagador Teste Um", $primeiroBoleto->pagador->nome);
    }

    public function testDeveCriarAPartirDeJsonValidoSemPaginacao()
    {
        $json = '{
            "resultado": [
                {
                    "numeroContrato": "123456",
                    "nossoNumero": "0011223344",
                    "dataVencimento": "2023-10-30",
                    "valor": "150.75"
                }
            ]
        }';

        $boletoResponse = BoletoResponse::createFromJson($json);

        $this->assertInstanceOf(BoletoResponse::class, $boletoResponse);
        $this->assertCount(1, $boletoResponse->boletos);
        $this->assertNull($boletoResponse->totalPaginas);
        $this->assertNull($boletoResponse->totalRegistros);
        $this->assertEquals("123456", $boletoResponse->boletos[0]->numeroContrato);
    }
    
    public function testDeveLidarComJsonVazioOuInvalido()
    {
        $boletoResponseVazio = BoletoResponse::createFromJson("[]"); // Empty array
        $this->assertCount(0, $boletoResponseVazio->boletos);

        $boletoResponseResultadoVazio = BoletoResponse::createFromJson('{"resultado": []}');
        $this->assertCount(0, $boletoResponseResultadoVazio->boletos);
        $this->assertNull($boletoResponseResultadoVazio->totalPaginas);

        // Malformed JSON - createFromJson in BoletoResponse is expected to handle this gracefully (e.g. empty object)
        // or one might expect an exception depending on the desired behavior in BoletoResponse.
        // Based on current BoletoResponse, it would result in empty boletos array.
        $boletoResponseInvalido = BoletoResponse::createFromJson('{"malformed": "json"}');
        $this->assertCount(0, $boletoResponseInvalido->boletos);
    }
}
