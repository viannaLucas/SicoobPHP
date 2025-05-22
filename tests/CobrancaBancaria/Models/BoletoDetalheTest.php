<?php

namespace viannaLucas\SicoobPhp\Tests\CobrancaBancaria\Models;

use PHPUnit\Framework\TestCase;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoDetalhe;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\Pagador;

class BoletoDetalheTest extends TestCase
{
    public function testDeveCriarAPartirDeJsonValidoParaBoletoIndividual()
    {
        $json = '{
            "resultado": {
                "numeroContrato": "987654",
                "modalidade": 1,
                "numeroContaCorrente": "1234567",
                "especieDocumento": "DM",
                "dataEmissao": "2023-11-01",
                "nossoNumero": "0099887766",
                "seuNumero": "SN003",
                "identificacaoBoletoEmpresa": "IDEMPRESA003",
                "codigoBarras": "12345678901234567890123456789012345678901234",
                "linhaDigitavel": "001900000901234000009000000000009123456789000",
                "valor": "350.25",
                "dataVencimento": "2023-11-30",
                "dataLimitePagamento": "2023-12-10",
                "valorAbatimento": "0.00",
                "situacaoBoleto": "Em Aberto",
                "pagador": {
                    "nome": "Pagador Teste Individual",
                    "numeroCpfCnpj": "12312312300",
                    "email": "pagador.individual@example.com"
                },
                "sacadorAvalista": {
                    "nome": "Sacador Avalista Teste",
                    "numeroCpfCnpj": "32132132100"
                }
            }
        }';

        // BoletoDetalhe::createFromJson expects the content of "resultado" if it exists,
        // or the root object if "resultado" is not present.
        $data = json_decode($json, true);
        $boletoDetalhe = BoletoDetalhe::createFromJson(json_encode($data['resultado']));

        $this->assertInstanceOf(BoletoDetalhe::class, $boletoDetalhe);
        $this->assertEquals("987654", $boletoDetalhe->numeroContrato);
        $this->assertEquals("0099887766", $boletoDetalhe->nossoNumero);
        $this->assertEquals("SN003", $boletoDetalhe->seuNumero);
        $this->assertEquals("350.25", $boletoDetalhe->valor);
        $this->assertEquals("Em Aberto", $boletoDetalhe->situacaoBoleto);

        $this->assertInstanceOf(Pagador::class, $boletoDetalhe->pagador);
        $this->assertEquals("Pagador Teste Individual", $boletoDetalhe->pagador->nome);
        $this->assertEquals("12312312300", $boletoDetalhe->pagador->numeroCpfCnpj);
        $this->assertEquals("pagador.individual@example.com", $boletoDetalhe->pagador->email);
        
        $this->assertInstanceOf(Pagador::class, $boletoDetalhe->sacadorAvalista);
        $this->assertEquals("Sacador Avalista Teste", $boletoDetalhe->sacadorAvalista->nome);
    }

    public function testDeveCriarAPartirDeJsonValidoSemResultadoWrapper()
    {
        $json = '{
            "numeroContrato": "987654",
            "nossoNumero": "0099887766",
            "valor": "350.25",
            "situacaoBoleto": "Em Aberto"
        }';

        $boletoDetalhe = BoletoDetalhe::createFromJson($json);

        $this->assertInstanceOf(BoletoDetalhe::class, $boletoDetalhe);
        $this->assertEquals("987654", $boletoDetalhe->numeroContrato);
        $this->assertEquals("0099887766", $boletoDetalhe->nossoNumero);
        $this->assertEquals("350.25", $boletoDetalhe->valor);
        $this->assertNull($boletoDetalhe->pagador); // Pagador not in this JSON
    }
    
    public function testDeveLidarComJsonVazioOuInvalido()
    {
        // Malformed JSON
        $boletoDetalheInvalido = BoletoDetalhe::createFromJson('{"malformed": "json"}');
        $this->assertInstanceOf(BoletoDetalhe::class, $boletoDetalheInvalido);
        $this->assertNull($boletoDetalheInvalido->numeroContrato); // Expect properties to be null

        // Empty JSON object
        $boletoDetalheVazio = BoletoDetalhe::createFromJson('{}');
        $this->assertInstanceOf(BoletoDetalhe::class, $boletoDetalheVazio);
        $this->assertNull($boletoDetalheVazio->numeroContrato);
    }
}
