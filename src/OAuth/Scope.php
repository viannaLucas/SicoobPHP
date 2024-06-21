<?php

namespace viannaLucas\SicoobPhp\OAuth;

enum Scope: string
{
    case ExtratoContaCorrente = 'cco_extrato'; 
    case SaldoContaCorrente = 'cco_saldo'; 
    case ConsultaContaCorrente = 'cco_consulta'; 
    case TranferenciaContaCorrente = 'cco_transferencias'; 

    /**
     * 
     * @param array<Scope> $scopes
     * @return string
     */
    public static function convertToString(array $scopes): string
    {
        $scopeValues = [];
        foreach($scopes as $scope) {
            $scopeValues[] = $scope->value;
        }
        return implode(' ', $scopeValues);
    }
}
