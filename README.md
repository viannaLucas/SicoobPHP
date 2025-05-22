# SicoobPHP
Integracao com Dados da Conta Sicoob com PHP

Este SDK fornece uma interface para interagir com as APIs do Sicoob, facilitando a integração de funcionalidades como Cobrança Bancária em aplicações PHP.

## Instalação

(Instruções de instalação a serem adicionadas - por exemplo, via Composer)

```bash
composer require seu-vendor/sicoob-php
```

## Configuração

Para usar o SDK, você precisará de suas credenciais da API do Sicoob (Client ID, Client Secret) e os caminhos para seus arquivos de certificado (.pem) e chave privada (.key).

```php
<?php
require 'vendor/autoload.php';

use viannaLucas\SicoobPhp\ApiConfiguration;
use viannaLucas\SicoobPhp\OAuth\NullTokenPersistence; // Ou sua própria estratégia de persistência

$config = new ApiConfiguration(
    clientId: 'SEU_CLIENT_ID',
    clientSecret: 'SEU_CLIENT_SECRET',
    scopes: [
        \viannaLucas\SicoobPhp\OAuth\Scope::COB_BOLETO_CONSULTAR, // Escopo para consultar boletos
        \viannaLucas\SicoobPhp\OAuth\Scope::COB_BOLETO_INCLUIR,   // Escopo para incluir boletos
        // Adicione outros escopos conforme necessário
    ],
    fileCertPath: '/caminho/para/seu/certificado.pem',
    fileCertKey: '/caminho/para/sua/chave.key'
);

// Para persistência de token (opcional, mas recomendado para produção):
// $tokenPersistence = new SeuTokenPersistenceStrategy();
$tokenPersistence = new NullTokenPersistence(); // Exemplo simples sem persistência

?>
```

## API de Cobrança Bancária v3

O SDK agora suporta a API de Cobrança Bancária v3 do Sicoob, permitindo a consulta e inclusão de boletos.

Para começar a usar a API de Cobrança Bancária, instancie a classe `ApiCobrancaBancaria`:

```php
<?php
// ... (após a configuração do $config e $tokenPersistence)

use viannaLucas\SicoobPhp\CobrancaBancaria\ApiCobrancaBancaria;

$apiCobranca = new ApiCobrancaBancaria($config, $tokenPersistence);

?>
```

### Consultar Boletos

Você pode consultar boletos existentes utilizando diversos filtros.

O método `consultarBoletos(array $params)` retorna um objeto `BoletoResponse` que contém uma lista de boletos e informações de paginação.

**Parâmetros de consulta comuns:**

*   `numeroContrato`: Número do contrato de cobrança.
*   `modalidade`: Modalidade do boleto (ex: 1 para Simples Com Registro).
*   `dataVencimentoInicio`: Data de vencimento inicial (formato: YYYY-MM-DD).
*   `dataVencimentoFim`: Data de vencimento final (formato: YYYY-MM-DD).
*   `nossoNumero`: O Nosso Número do boleto.
*   `seuNumero`: O Seu Número (número do documento) do boleto.
*   `cpfCnpjPagador`: CPF ou CNPJ do pagador.
*   `situacaoBoleto`: Situação do boleto (ex: `EmAberto`, `Baixado`, `Liquidado`).

**Exemplo de uso:**

```php
<?php
// ... ($apiCobranca instanciado)

try {
    $parametrosConsulta = [
        'numeroContrato' => '1234567',
        'dataVencimentoInicio' => '2024-01-01',
        'dataVencimentoFim' => '2024-12-31',
        'situacaoBoleto' => 'EmAberto'
    ];

    $response = $apiCobranca->consultarBoletos($parametrosConsulta);

    echo "Total de páginas: " . ($response->totalPaginas ?? 'N/A') . "\n";
    echo "Total de registros: " . ($response->totalRegistros ?? 'N/A') . "\n\n";

    foreach ($response->boletos as $boleto) {
        echo "Nosso Número: " . $boleto->nossoNumero . "\n";
        echo "Seu Número: " . $boleto->seuNumero . "\n";
        echo "Data de Vencimento: " . $boleto->dataVencimento . "\n";
        echo "Valor: " . $boleto->valor . "\n";
        if ($boleto->pagador) {
            echo "Pagador: " . $boleto->pagador->nome . "\n";
        }
        echo "-----------------------------\n";
    }

} catch (\Exception $e) {
    echo "Erro ao consultar boletos: " . $e->getMessage();
}
?>
```

### Consultar Boleto por ID (Seu Número)

Para consultar um boleto específico utilizando o "Seu Número" (identificador único do boleto para o cliente).

O método `consultarBoletoPorId(string $seuNumero)` retorna um objeto `BoletoDetalhe`.

**Exemplo de uso:**

```php
<?php
// ... ($apiCobranca instanciado)

try {
    $seuNumeroParaConsulta = 'SN20240001'; // Substitua pelo "Seu Número" desejado
    $boletoDetalhe = $apiCobranca->consultarBoletoPorId($seuNumeroParaConsulta);

    if ($boletoDetalhe) {
        echo "Detalhes do Boleto (Seu Número: " . $boletoDetalhe->seuNumero . "):\n";
        echo "Nosso Número: " . $boletoDetalhe->nossoNumero . "\n";
        echo "Data de Vencimento: " . $boletoDetalhe->dataVencimento . "\n";
        echo "Valor: " . $boletoDetalhe->valor . "\n";
        echo "Situação: " . $boletoDetalhe->situacaoBoleto . "\n";
        if ($boletoDetalhe->pagador) {
            echo "Pagador: " . $boletoDetalhe->pagador->nome . " (CPF/CNPJ: " . $boletoDetalhe->pagador->numeroCpfCnpj . ")\n";
        }
        // Acesse outras propriedades conforme necessário
    } else {
        echo "Boleto com Seu Número '$seuNumeroParaConsulta' não encontrado.\n";
    }

} catch (\Exception $e) {
    echo "Erro ao consultar boleto por ID: " . $e->getMessage();
}
?>
```

### Incluir Boletos

Permite o registro de novos boletos na plataforma do Sicoob.

O método `incluirBoletos(array $boletosRequest)` aceita um array de objetos `BoletoRequest`. Cada objeto `BoletoRequest` representa um boleto a ser incluído.

**Criando um `BoletoRequest`:**

```php
<?php
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\PagadorRequest;
// Outros DTOs como BeneficiarioFinalRequest, MensagemInstrucao podem ser usados também.

$pagador = new PagadorRequest(
    nome: 'Nome do Pagador Exemplo',
    numeroCpfCnpj: '12345678901', // CPF ou CNPJ
    logradouro: 'Rua Exemplo, 123',
    bairro: 'Centro',
    cidade: 'Cidade Exemplo',
    cep: '12345000',
    uf: 'UF',
    email: 'pagador@example.com' 
    // O campo 'endereco' será montado automaticamente com logradouro, numero e complemento.
);

$boletoParaIncluir = new BoletoRequest(
    numeroContrato: '1234567', // Seu número de contrato de cobrança
    modalidade: 1, // 1 para Simples com Registro
    numeroContaCorrente: '987654', // Sua conta corrente
    especieDocumento: 'DM', // Duplicata Mercantil
    dataEmissao: date('Y-m-d\TH:i:sP'), // Data atual com timezone
    seuNumero: 'SN20240002', // Identificador único do boleto para você
    codigoAceite: 'A', // 'A' para Aceite, 'N' para Não Aceite
    valor: 150.75,
    dataVencimento: '2024-12-31T00:00:00-03:00', // Formato: YYYY-MM-DDTHH:MM:SSOFFSET
    pagador: $pagador
);

// Campos opcionais podem ser definidos:
$boletoParaIncluir->identificacaoBoletoEmpresa = 'Pedido XPTO-123'; // Campo livre
$boletoParaIncluir->valorMulta = 5.00; // Valor fixo da multa
$boletoParaIncluir->dataMulta = '2025-01-01'; // Data para início da multa
// ... outros campos opcionais (descontos, juros, etc.)

$boletosParaEnviar = [$boletoParaIncluir];
// Você pode adicionar múltiplos objetos BoletoRequest a este array para inclusão em lote.
?>
```

**Chamando `incluirBoletos` e interpretando a resposta:**

O método retorna um objeto `IncluirBoletosResponse`.

```php
<?php
// ... ($apiCobranca instanciado e $boletosParaEnviar preparado)

try {
    $responseInclusao = $apiCobranca->incluirBoletos($boletosParaEnviar);

    echo "HTTP Status da Inclusão: " . $responseInclusao->statusHttp . "\n";

    if ($responseInclusao->status) {
        echo "Status Geral: (" . $responseInclusao->status->codigo . ") " . $responseInclusao->status->mensagem . "\n";
    }

    // Processar resultados individuais (sucesso ou falha por boleto)
    if (!empty($responseInclusao->resultados)) {
        echo "\nResultados da Inclusão:\n";
        foreach ($responseInclusao->resultados as $resultado) {
            if (isset($resultado->status->codigo) && $resultado->status->codigo == 0) { // Sucesso para este boleto
                echo "  Boleto (Seu Número: " . ($resultado->boleto->seuNumero ?? 'N/A') . ") incluído com sucesso!\n";
                echo "    Nosso Número: " . ($resultado->boleto->nossoNumero ?? 'N/A') . "\n";
                echo "    Linha Digitável: " . ($resultado->boleto->linhaDigitavel ?? 'N/A') . "\n";
            } else { // Falha ou status diferente para este boleto
                echo "  Boleto (Seu Número: " . ($resultado->boleto->seuNumero ?? 'N/A') . ") - Status: (" . ($resultado->status->codigo ?? 'N/A') . ") " . ($resultado->status->mensagem ?? 'Erro desconhecido') . "\n";
            }
        }
    }

    // Processar erros gerais da requisição (ex: falha de autenticação, erros de validação globais)
    if (!empty($responseInclusao->erros)) {
        echo "\nErros na Requisição:\n";
        foreach ($responseInclusao->erros as $erro) {
            echo "  Código: " . $erro->codigo . " - Mensagem: " . $erro->mensagem . "\n";
            if ($erro->detalhe) {
                echo "    Detalhe: " . $erro->detalhe . "\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "Erro ao incluir boletos: " . $e->getMessage();
}
?>
```
