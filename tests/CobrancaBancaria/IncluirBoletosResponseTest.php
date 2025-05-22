<?php

namespace viannaLucas\SicoobPhp\Tests\CobrancaBancaria;

use PHPUnit\Framework\TestCase;
use viannaLucas\SicoobPhp\CobrancaBancaria\IncluirBoletosResponse;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\ResultadoInclusaoBoleto;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\ErroInclusaoBoleto;

class IncluirBoletosResponseTest extends TestCase
{
    public function testCreateFromJsonSucessoMultiStatus207()
    {
        $json = '{
            "status": {"codigo": 207, "mensagem": "Processado com múltiplos status."},
            "resultado": [
                {
                    "status": {"codigo": 0, "mensagem": "Incluído com Sucesso"},
                    "boleto": {"seuNumero": "SN001", "nossoNumero": "12345", "codigoBarras": "CB001", "linhaDigitavel": "LD001"}
                },
                {
                    "status": {"codigo": 1, "mensagem": "Boleto já existente."},
                    "boleto": {"seuNumero": "SN002", "nossoNumero": "67890"}
                }
            ]
        }';

        $response = IncluirBoletosResponse::createFromJson($json, 207);

        $this->assertInstanceOf(IncluirBoletosResponse::class, $response);
        $this->assertEquals(207, $response->statusHttp);
        $this->assertNotNull($response->status);
        $this->assertEquals(207, $response->status->codigo);

        $this->assertCount(2, $response->resultados);
        
        $this->assertInstanceOf(ResultadoInclusaoBoleto::class, $response->resultados[0]);
        $this->assertEquals(0, $response->resultados[0]->status->codigo);
        $this->assertEquals("SN001", $response->resultados[0]->boleto->seuNumero);
        $this->assertEquals("12345", $response->resultados[0]->boleto->nossoNumero);

        $this->assertInstanceOf(ResultadoInclusaoBoleto::class, $response->resultados[1]);
        $this->assertEquals(1, $response->resultados[1]->status->codigo); // Error for this specific boleto
        $this->assertEquals("Boleto já existente.", $response->resultados[1]->status->mensagem);
        $this->assertEquals("SN002", $response->resultados[1]->boleto->seuNumero);
        
        $this->assertEmpty($response->erros); // No general errors, only per-item status
    }

    public function testCreateFromJsonSucessoSimples201()
    {
        $json = '{
            "resultado": {
                "status": {"codigo": 201, "mensagem": "Boleto incluído com sucesso."},
                "boleto": {"seuNumero": "SN003", "nossoNumero": "112233", "codigoBarras": "CB003", "linhaDigitavel": "LD003"}
            }
        }';
        // Note: The Sicoob API sometimes returns 200 for single success, but 201 is more standard for "Created"
        $response = IncluirBoletosResponse::createFromJson($json, 201);

        $this->assertEquals(201, $response->statusHttp);
        $this->assertCount(1, $response->resultados);
        $this->assertEmpty($response->erros);

        $this->assertInstanceOf(ResultadoInclusaoBoleto::class, $response->resultados[0]);
        $this->assertEquals(201, $response->resultados[0]->status->codigo); // Assuming status within "resultado" reflects the specific item
        $this->assertEquals("SN003", $response->resultados[0]->boleto->seuNumero);
    }
    
    public function testCreateFromJsonSucessoSimples200ComStatusGeral()
    {
        // Sicoob often returns 200 OK with a general status object and the result
        $json = '{
            "status": {"codigo": 200, "mensagem": "Solicitação processada com sucesso."},
            "resultado": {
                "boleto": {"seuNumero": "SN004", "nossoNumero": "445566"}
            }
        }';
        $response = IncluirBoletosResponse::createFromJson($json, 200);

        $this->assertEquals(200, $response->statusHttp);
        $this->assertNotNull($response->status);
        $this->assertEquals(200, $response->status->codigo);
        $this->assertCount(1, $response->resultados);
        $this->assertEmpty($response->erros);
        $this->assertEquals("SN004", $response->resultados[0]->boleto->seuNumero);

    }


    public function testCreateFromJsonErroValidacao422ComErros()
    {
        $json = '{
            "status": {"codigo": 422, "mensagem": "Erro de validação"},
            "erros": [
                {"codigo": "CAMPO_OBRIGATORIO", "mensagem": "O campo \'pagador.nome\' é obrigatório."},
                {"codigo": "FORMATO_INVALIDO", "mensagem": "O campo \'dataVencimento\' não está no formato esperado."}
            ]
        }';
        $response = IncluirBoletosResponse::createFromJson($json, 422);

        $this->assertEquals(422, $response->statusHttp);
        $this->assertNotNull($response->status);
        $this->assertEquals(422, $response->status->codigo);
        $this->assertCount(2, $response->erros);
        $this->assertEmpty($response->resultados);

        $this->assertInstanceOf(ErroInclusaoBoleto::class, $response->erros[0]);
        $this->assertEquals("CAMPO_OBRIGATORIO", $response->erros[0]->codigo);
        $this->assertEquals("O campo 'pagador.nome' é obrigatório.", $response->erros[0]->mensagem);
    }
    
    public function testCreateFromJsonErroValidacao400ComViolacoes()
    {
        // Sicoob uses "violacoes" in some 400 error responses
        $json = '{
            "violacoes": [
                {
                    "razao": "EspecieDocumento inválido.",
                    "propriedade": "especieDocumento",
                    "mensagem": "O valor \'XYZ\' não é válido."
                }
            ]
        }';
        $response = IncluirBoletosResponse::createFromJson($json, 400);
        $this->assertEquals(400, $response->statusHttp);
        $this->assertCount(1, $response->erros);
        $this->assertEmpty($response->resultados);
        $this->assertEquals("EspecieDocumento inválido.", $response->erros[0]->codigo); // Mapped from 'razao'
        $this->assertEquals("especieDocumento: O valor 'XYZ' não é válido.", $response->erros[0]->mensagem);
    }
    
    public function testCreateFromJsonErro401ComMessage()
    {
        // Sicoob uses "message" in 401 error responses
        $json = '{
            "message": "Token de acesso inválido ou expirado."
        }';
        $response = IncluirBoletosResponse::createFromJson($json, 401);
        $this->assertEquals(401, $response->statusHttp);
        $this->assertCount(1, $response->erros);
        $this->assertEmpty($response->resultados);
        $this->assertEquals("401", $response->erros[0]->codigo); 
        $this->assertEquals("Token de acesso inválido ou expirado.", $response->erros[0]->mensagem);
    }


    public function testCreateFromJsonComJsonInvalido()
    {
        $invalidJson = '{"malformed": "json",, "extraComma": true}';
        $response = IncluirBoletosResponse::createFromJson($invalidJson, 500); // HTTP status could be anything if body is invalid

        $this->assertEquals(500, $response->statusHttp);
        $this->assertCount(1, $response->erros);
        $this->assertInstanceOf(ErroInclusaoBoleto::class, $response->erros[0]);
        $this->assertEquals("JSON_DECODE_ERROR", $response->erros[0]->codigo);
        $this->assertStringContainsString("Falha ao decodificar o corpo da resposta JSON.", $response->erros[0]->mensagem);
        $this->assertEquals($invalidJson, $response->erros[0]->detalhe);
        $this->assertEmpty($response->resultados);
    }
    
    public function testCreateFromJsonComJsonCorpoVazio()
    {
        $emptyJson = "";
        $response = IncluirBoletosResponse::createFromJson($emptyJson, 204); // No Content

        $this->assertEquals(204, $response->statusHttp);
        $this->assertCount(1, $response->erros); // createFromJson adds a JSON_DECODE_ERROR for empty string
        $this->assertEquals("JSON_DECODE_ERROR", $response->erros[0]->codigo);
        $this->assertEmpty($response->resultados);
    }
}
