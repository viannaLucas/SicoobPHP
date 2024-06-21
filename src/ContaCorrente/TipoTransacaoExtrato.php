<?php

namespace viannaLucas\SicoobPhp\ContaCorrente;

enum TipoTransacaoExtrato: string
{

    case DEBITO = 'DEBITO';
    case CREDITO = 'CREDITO';
}