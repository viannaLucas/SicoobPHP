<?php

namespace viannaLucas\SicoobPhp\Tests\CobrancaBancaria;

use PHPUnit\Framework\TestCase;
use viannaLucas\SicoobPhp\ApiConfiguration;
use viannaLucas\SicoobPhp\OAuth\TokenPersistenceStrategy;
use viannaLucas\SicoobPhp\CobrancaBancaria\ApiCobrancaBancaria;
use viannaLucas\SicoobPhp\Response;
use viannaLucas\SicoobPhp\CobrancaBancaria\BoletoResponse;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoDetalhe;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\PagadorRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\IncluirBoletosResponse;
use Exception; // For expecting exceptions
use InvalidArgumentException;

class ApiCobrancaBancariaTest extends TestCase
{
    private $apiConfigurationMock;
    private $tokenPersistenceMock;
    private $apiCobrancaMock;

    protected function setUp(): void
    {
        $this->apiConfigurationMock = $this->createMock(ApiConfiguration::class);
        $this->tokenPersistenceMock = $this->createMock(TokenPersistenceStrategy::class);

        // It's often better to mock specific methods of the class under test if they make external calls (like HTTP requests)
        // For getRequest and postRequest, these are part of ApiSicoob, which ApiCobrancaBancaria extends.
        // We will mock these methods directly on the ApiCobrancaBancaria mock instance.
        $this->apiCobrancaMock = $this->getMockBuilder(ApiCobrancaBancaria::class)
            ->setConstructorArgs([$this->apiConfigurationMock, $this->tokenPersistenceMock])
            ->onlyMethods(['getRequest', 'postRequest']) // Mock only these methods, rest will be original
            ->getMock();
            
        // Define the constant if not already defined
        if (!defined('viannaLucas\SicoobPhp\ApiConfiguration::BASE_URL_COBRANCA_BANCARIA')) {
            define('viannaLucas\SicoobPhp\ApiConfiguration::BASE_URL_COBRANCA_BANCARIA', 'https://dummy.sicoob.com.br/cobranca-bancaria/v3');
        }
    }

    public function testDeveInstanciarCorretamente()
    {
        // apiCobrancaMock is already an instance, created in setUp
        $this->assertInstanceOf(ApiCobrancaBancaria::class, $this->apiCobrancaMock);
    }

    // Tests for consultarBoletos()
    public function testConsultarBoletosSucesso()
    {
        $params = ['seuNumero' => '12345'];
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos'; // Params are query string, not part of base URL for getRequest mock expectation here
        
        $mockJsonResponse = '{
            "resultado": [{"nossoNumero": "111"}],
            "paginacao": {"quantidadePaginas": 1, "quantidadeRegistros": 1}
        }';
        
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockJsonResponse);
        $mockResponse->method('getHttpCode')->willReturn(200);

        $this->apiCobrancaMock->expects($this->once())
            ->method('getRequest')
            ->with($expectedUrl, $params) // Ensure URL and params are correctly passed
            ->willReturn($mockResponse);
            
        $result = $this->apiCobrancaMock->consultarBoletos($params);

        $this->assertInstanceOf(BoletoResponse::class, $result);
        $this->assertCount(1, $result->boletos);
        $this->assertEquals("111", $result->boletos[0]->nossoNumero);
    }

    public function testConsultarBoletosErroApi()
    {
        $this->expectException(Exception::class);
        // As validateResponse in ApiSicoob throws a generic Exception
        // We can make this more specific if we had a custom API exception type

        $params = ['seuNumero' => '12345'];
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos';

        $mockErrorResponse = $this->createMock(Response::class);
        $mockErrorResponse->method('getBody')->willReturn('{"error": "API Error"}');
        $mockErrorResponse->method('getHttpCode')->willReturn(500);

        $this->apiCobrancaMock->expects($this->once())
            ->method('getRequest')
            ->with($expectedUrl, $params)
            ->willReturn($mockErrorResponse);
        
        // The original validateResponse method from ApiSicoob (parent of ApiCobrancaBancaria)
        // should be called and throw an exception due to the 500 HTTP code.
        // If validateResponse itself was mocked, we'd configure it to throw new Exception().
        
        $this->apiCobrancaMock->consultarBoletos($params);
    }

    // Tests for consultarBoletoPorId()
    public function testConsultarBoletoPorIdSucesso()
    {
        $seuNumero = '123';
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . "/boletos/{$seuNumero}";
        
        $mockJsonResponse = '{"resultado": {"nossoNumero": "222", "valor": "150.00"}}';
        
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockJsonResponse);
        $mockResponse->method('getHttpCode')->willReturn(200);

        $this->apiCobrancaMock->expects($this->once())
            ->method('getRequest')
            ->with($expectedUrl, []) // No query params for this specific endpoint
            ->willReturn($mockResponse);
            
        $result = $this->apiCobrancaMock->consultarBoletoPorId($seuNumero);

        $this->assertInstanceOf(BoletoDetalhe::class, $result);
        $this->assertEquals("222", $result->nossoNumero);
        $this->assertEquals("150.00", $result->valor);
    }

    public function testConsultarBoletoPorIdErroApi()
    {
        $this->expectException(Exception::class);

        $seuNumero = '123';
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . "/boletos/{$seuNumero}";

        $mockErrorResponse = $this->createMock(Response::class);
        $mockErrorResponse->method('getBody')->willReturn('{"error": "Not Found"}');
        $mockErrorResponse->method('getHttpCode')->willReturn(404);

        $this->apiCobrancaMock->expects($this->once())
            ->method('getRequest')
            ->with($expectedUrl, [])
            ->willReturn($mockErrorResponse);
            
        $this->apiCobrancaMock->consultarBoletoPorId($seuNumero);
    }

    // Tests for incluirBoletos()
    private function criarBoletoRequestExemplo(): BoletoRequest
    {
        // Helper to create a valid BoletoRequest for tests
        $pagadorRequest = new PagadorRequest(
            nome: 'Pagador Teste',
            numeroCpfCnpj: '12345678901',
            logradouro: 'Rua Teste',
            numero: '123',
            bairro: 'Bairro Teste',
            cidade: 'Cidade Teste',
            cep: '12345000',
            uf: 'SP'
        );
        return new BoletoRequest(
            numeroContrato: '123456',
            modalidade: 1,
            numeroContaCorrente: '1234567',
            especieDocumento: 'DM',
            dataEmissao: '2024-01-01T10:00:00-03:00',
            seuNumero: 'SN12345',
            codigoAceite: 'A',
            valor: 100.50,
            dataVencimento: '2024-02-01T00:00:00-03:00',
            pagador: $pagadorRequest
        );
    }

    public function testIncluirBoletosSucesso()
    {
        $boletoRequest1 = $this->criarBoletoRequestExemplo();
        $boletoRequest2 = $this->criarBoletoRequestExemplo();
        $boletoRequest2->seuNumero = 'SN12346'; // Make it slightly different

        $boletosParaIncluir = [$boletoRequest1, $boletoRequest2];
        $payloadEsperado = array_map(fn($br) => $br->toArray(), $boletosParaIncluir);
        
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos';
        
        $mockJsonResponse = '{
            "status": {"codigo": 207, "mensagem": "Processado com múltiplos status."},
            "resultado": [
                {"status": {"codigo": 0, "mensagem": "Incluído com Sucesso"}, "boleto": {"seuNumero": "SN12345", "nossoNumero": "111"}},
                {"status": {"codigo": 0, "mensagem": "Incluído com Sucesso"}, "boleto": {"seuNumero": "SN12346", "nossoNumero": "222"}}
            ]
        }';
        
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getBody')->willReturn($mockJsonResponse);
        $mockResponse->method('getHttpCode')->willReturn(207); // Multi-Status

        $this->apiCobrancaMock->expects($this->once())
            ->method('postRequest')
            ->with($expectedUrl, $payloadEsperado)
            ->willReturn($mockResponse);
            
        $result = $this->apiCobrancaMock->incluirBoletos($boletosParaIncluir);

        $this->assertInstanceOf(IncluirBoletosResponse::class, $result);
        $this->assertEquals(207, $result->statusHttp);
        $this->assertCount(2, $result->resultados);
        $this->assertEmpty($result->erros);
        $this->assertEquals("SN12345", $result->resultados[0]->boleto->seuNumero);
        $this->assertEquals("111", $result->resultados[0]->boleto->nossoNumero);
    }

    public function testIncluirBoletosErroApi()
    {
        $this->expectException(Exception::class); // Assuming validateResponse throws generic Exception

        $boletoRequest = $this->criarBoletoRequestExemplo();
        $payloadEsperado = [$boletoRequest->toArray()];
        $expectedUrl = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos';

        $mockErrorResponse = $this->createMock(Response::class);
        $mockErrorResponse->method('getBody')->willReturn('{"erros": [{"codigo": "VALIDATION_ERROR", "mensagem": "Erro de validação"}]}');
        $mockErrorResponse->method('getHttpCode')->willReturn(422); // Unprocessable Entity

        $this->apiCobrancaMock->expects($this->once())
            ->method('postRequest')
            ->with($expectedUrl, $payloadEsperado)
            ->willReturn($mockErrorResponse);
            
        $this->apiCobrancaMock->incluirBoletos([$boletoRequest]);
    }
    
    public function testIncluirBoletosComArgumentoInvalido()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos os itens em $boletos devem ser instâncias de BoletoRequest.');

        $boletosInvalidos = [$this->criarBoletoRequestExemplo(), new \stdClass()];
        
        // postRequest should not even be called
        $this->apiCobrancaMock->expects($this->never())->method('postRequest');
        
        $this->apiCobrancaMock->incluirBoletos($boletosInvalidos);
    }
}
