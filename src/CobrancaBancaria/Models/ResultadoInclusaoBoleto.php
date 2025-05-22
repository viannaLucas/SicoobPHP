<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class ResultadoInclusaoBoleto
{
    public ?object $status = null; // Could be {codigo, mensagem}
    public ?object $boleto = null; // Could contain fields like numeroCliente, codigoNossoNumero, etc.

    public function __construct(?object $status = null, ?object $boleto = null)
    {
        $this->status = $status;
        $this->boleto = $boleto;
    }

    public static function createFromArray(array $data): self
    {
        $status = isset($data['status']) && is_array($data['status']) ? (object) $data['status'] : ($data['status'] ?? null);
        $boleto = isset($data['boleto']) && is_array($data['boleto']) ? (object) $data['boleto'] : ($data['boleto'] ?? null);
        
        // If 'boleto' is not set, but other relevant keys are at the root of $data, map them.
        // This handles cases where the "boleto" sub-object might be flattened in the response.
        if ($boleto === null && (isset($data['codigoNossoNumero']) || isset($data['numeroCliente']))) {
            $boletoData = [
                'numeroCliente' => $data['numeroCliente'] ?? null,
                'codigoNossoNumero' => $data['codigoNossoNumero'] ?? null,
                'codigoBarras' => $data['codigoBarras'] ?? null,
                'linhaDigitavel' => $data['linhaDigitavel'] ?? null,
                'seuNumero' => $data['seuNumero'] ?? null,
                'numeroContrato' => $data['numeroContrato'] ?? null,
            ];
            $boleto = (object) array_filter($boletoData, fn($value) => $value !== null);
        }


        return new self($status, $boleto);
    }
}
