<?php

namespace viannaLucas\SicoobPhp\CobrancaBancaria\Models;

class MensagemInstrucao
{
    public ?int $tipoInstrucao = null;
    /** @var string[] */
    public array $mensagens = [];

    public function __construct(?int $tipoInstrucao = null, array $mensagens = [])
    {
        $this->tipoInstrucao = $tipoInstrucao;
        $this->mensagens = $mensagens;
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->tipoInstrucao !== null) {
            $data['tipoInstrucao'] = $this->tipoInstrucao;
        }
        if (!empty($this->mensagens)) {
            $data['mensagens'] = $this->mensagens;
        }
        return $data;
    }
}
