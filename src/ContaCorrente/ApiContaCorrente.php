<?php

namespace viannaLucas\SicoobPhp\ContaCorrente;

use viannaLucas\SicoobPhp\ApiSicoob;

class ApiContaCorrente extends ApiSicoob
{

    public function extrato(int $mes, int $ano, int $numeroContaCorrente): Extrato
    {
        if($ano < 100){
            $ano += 2000;
        }
        $r = $this->getRequest("conta-corrente/v4/extrato/$mes/$ano", ['numeroContaCorrente' => $numeroContaCorrente]);
        $this->validateResponse($r);
        return Extrato::createFromJson($r->getBody());
    }
    
}