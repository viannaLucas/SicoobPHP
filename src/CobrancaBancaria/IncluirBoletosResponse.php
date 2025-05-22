<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria;

use viannaLucas\SicoobPhp\CobrancaBancaria\Models\ResultadoInclusaoBoleto;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\ErroInclusaoBoleto;

class IncluirBoletosResponse
{
    public ?int $statusHttp = null;
    public ?object $status = null; // For general request status
    /** @var array<ResultadoInclusaoBoleto> */
    public array $resultados = [];
    /** @var array<ErroInclusaoBoleto> */
    public array $erros = [];

    public function __construct(int $httpStatusCode)
    {
        $this->statusHttp = $httpStatusCode;
    }

    public static function createFromJson(string $jsonBody, int $httpStatusCode): self
    {
        $response = new self($httpStatusCode);
        $data = json_decode($jsonBody, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            // JSON decoding error, potentially empty body or malformed JSON
            // Depending on requirements, this could throw an exception or set an error status
            $response->erros[] = new ErroInclusaoBoleto('JSON_DECODE_ERROR', 'Falha ao decodificar o corpo da resposta JSON.', $jsonBody);
            return $response;
        }
        
        // Populate general status if present at root level
        if (isset($data['status']) && is_array($data['status'])) {
            $response->status = (object) $data['status'];
        } elseif (isset($data['status'])) {
             $response->status = (object) ['mensagem' => $data['status']]; // Handle if status is just a string
        }


        // Populate resultados
        if (isset($data['resultado'])) {
            if (is_array($data['resultado']) && (isset($data['resultado'][0]) || empty($data['resultado']))) { // Check if it's a list of results or an empty list
                foreach ($data['resultado'] as $itemData) {
                    if(is_array($itemData)){
                        $response->resultados[] = ResultadoInclusaoBoleto::createFromArray($itemData);
                    }
                }
            } elseif (is_array($data['resultado'])) { // Single result object under "resultado"
                $response->resultados[] = ResultadoInclusaoBoleto::createFromArray($data['resultado']);
            }
            // If $data['resultado'] is a string or other type, it's ignored for $resultados
        }
        
        // Populate erros
        // Common Sicoob pattern for errors is an array under "mensagens" or "erros"
        $errorSources = [];
        if(isset($data['erros']) && is_array($data['erros'])){
            $errorSources = $data['erros'];
        } elseif (isset($data['mensagens']) && is_array($data['mensagens'])){ // Sicoob often uses "mensagens" for errors
             $errorSources = $data['mensagens'];
        } elseif (isset($data['violacoes']) && is_array($data['violacoes'])){ // Another common error key
            foreach ($data['violacoes'] as $violacao) {
                $response->erros[] = new ErroInclusaoBoleto(
                    $violacao['razao'] ?? 'VALIDATION_ERROR',
                    $violacao['propriedade'] . ': ' . $violacao['mensagem']
                );
            }
        }


        foreach ($errorSources as $errorData) {
            if(is_array($errorData)){
                 $response->erros[] = ErroInclusaoBoleto::createFromArray($errorData);
            }
        }
        
        // Handle cases where the root itself might be an error structure (e.g., 401 Unauthorized from Sicoob often has "message")
        if (empty($response->resultados) && empty($response->erros) && $httpStatusCode >= 400) {
            if(isset($data['message']) || isset($data['error_description']) || isset($data['error'])) {
                 $response->erros[] = new ErroInclusaoBoleto(
                    $data['error'] ?? (string)$httpStatusCode,
                    $data['message'] ?? $data['error_description'] ?? 'Erro desconhecido na requisição.'
                );
            } else if (!empty($data) && is_array($data) && count($data) === 1 && isset($data[0]) && is_array($data[0])) {
                // Sometimes errors come as an array with a single object inside
                $response->erros[] = ErroInclusaoBoleto::createFromArray($data[0]);
            } else if (empty($data) && !empty($jsonBody)){
                 $response->erros[] = new ErroInclusaoBoleto((string)$httpStatusCode, 'Resposta de erro não estruturada.', $jsonBody);
            }
        }


        return $response;
    }
}
