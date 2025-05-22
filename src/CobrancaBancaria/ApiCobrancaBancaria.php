<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria;

use viannaLucas\SicoobPhp\ApiConfiguration;
use viannaLucas\SicoobPhp\ApiSicoob;
use viannaLucas\SicoobPhp\CobrancaBancaria\BoletoResponse;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoDetalhe;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoRequest; // Already added in previous step for incluirBoletos parameters
use viannaLucas\SicoobPhp\CobrancaBancaria\IncluirBoletosResponse;
// Models\ResultadoInclusaoBoleto and Models\ErroInclusaoBoleto are used by IncluirBoletosResponse, not directly here.

class ApiCobrancaBancaria extends ApiSicoob
{
    public function consultarBoletos(array $parametros): BoletoResponse
    {
        $url = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos';
        $response = $this->getRequest($url, $parametros);
        $this->validateResponse($response);
        
        return BoletoResponse::createFromJson($response->getBody()->getContents());
    }

    public function consultarBoletoPorId(string $seuNumero): BoletoDetalhe
    {
        $url = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . "/boletos/{$seuNumero}";
        $response = $this->getRequest($url, []);
        $this->validateResponse($response);
        
        return BoletoDetalhe::createFromJson($response->getBody()->getContents());
    }

    /**
     * Inclui novos boletos.
     *
     * @param array<BoletoRequest> $boletos Array de objetos BoletoRequest.
     * @return IncluirBoletosResponse Resposta da API parseada em DTOs.
     * @throws \Exception
     */
    public function incluirBoletos(array $boletos): IncluirBoletosResponse
    {
        $url = ApiConfiguration::BASE_URL_COBRANCA_BANCARIA . '/boletos';
        
        $payload = [];
        foreach ($boletos as $boletoRequest) {
            if ($boletoRequest instanceof BoletoRequest) {
                $payload[] = $boletoRequest->toArray();
            } else {
                // Handle error or throw exception if an item is not a BoletoRequest instance
                throw new \InvalidArgumentException('Todos os itens em $boletos devem ser instâncias de BoletoRequest.');
            }
        }
        
        $response = $this->postRequest($url, $payload);
        
        // validateResponse in ApiSicoob now returns an object or throws an exception.
        // For now, we just care that it doesn't throw for success (200-299).
        // The actual response body might be useful for the client to know which boletos succeeded/failed if it's a partial success.
        // For now, returning the raw string body as requested.
        $this->validateResponse($response); // This will throw on non-2xx codes based on ApiSicoob's implementation.

        return IncluirBoletosResponse::createFromJson($response->getBody(), $response->getHttpCode());
    }
}
