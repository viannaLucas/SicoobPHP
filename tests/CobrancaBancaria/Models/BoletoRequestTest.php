<?php

namespace viannaLucas\SicoobPhp\Tests\CobrancaBancaria\Models;

use PHPUnit\Framework\TestCase;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BoletoRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\PagadorRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\BeneficiarioFinalRequest;
use viannaLucas\SicoobPhp\CobrancaBancaria\Models\MensagemInstrucao;

class BoletoRequestTest extends TestCase
{
    public function testToArrayConverteCorretamenteParaArray()
    {
        $pagador = new PagadorRequest(
            nome: 'Pagador Teste Completo',
            numeroCpfCnpj: '11122233344',
            logradouro: 'Rua Exemplo',
            numero: '100',
            bairro: 'Centro',
            cidade: 'Cidade Exemplo',
            cep: '12345000',
            uf: 'EX',
            email: 'pagador@example.com',
            complemento: 'Sala 1'
        );

        $beneficiario = new BeneficiarioFinalRequest(
            nome: 'Beneficiário Final Teste',
            numeroCpfCnpj: '55566677788'
        );

        $instrucoes = new MensagemInstrucao(
            tipoInstrucao: 1,
            mensagens: ['Não receber após o vencimento.', 'Sujeito a protesto.']
        );

        $boletoRequest = new BoletoRequest(
            numeroContrato: 'NC12345',
            modalidade: 1,
            numeroContaCorrente: 'CC67890',
            especieDocumento: 'DM',
            dataEmissao: '2024-03-15T10:00:00-03:00',
            seuNumero: 'SN0001',
            codigoAceite: 'A',
            valor: 123.45,
            dataVencimento: '2024-04-15T00:00:00-03:00',
            pagador: $pagador
        );

        // Populate optional fields
        $boletoRequest->identificacaoBoletoEmpresa = 'IDEMP001';
        $boletoRequest->beneficiarioFinal = $beneficiario;
        $boletoRequest->mensagensInstrucao = $instrucoes;
        $boletoRequest->codigoTipoMulta = 'VALORFIXO';
        $boletoRequest->dataMulta = '2024-04-16';
        $boletoRequest->valorMulta = 5.00;
        $boletoRequest->codigoTipoJurosMora = 'TAXAMENSAL';
        $boletoRequest->dataJurosMora = '2024-04-16';
        $boletoRequest->valorJurosMora = 0.50;
        $boletoRequest->codigoTipoDesconto = 'VALORFIXODATAINFORMADA';
        $boletoRequest->dataDesconto = '2024-03-30';
        $boletoRequest->valorDesconto = 10.00;
        $boletoRequest->numeroParcela = 1;
        $boletoRequest->quantidadeDiasProtesto = 10;
        $boletoRequest->quantidadeDiasNegativacao = 20;
        $boletoRequest->gerarPdf = "true";

        $arrayResultante = $boletoRequest->toArray();

        // Assertions for main properties
        $this->assertEquals('NC12345', $arrayResultante['numeroContrato']);
        $this->assertEquals(1, $arrayResultante['modalidade']);
        $this->assertEquals('CC67890', $arrayResultante['numeroContaCorrente']);
        $this->assertEquals('DM', $arrayResultante['especieDocumento']);
        $this->assertEquals('2024-03-15T10:00:00-03:00', $arrayResultante['dataEmissao']);
        $this->assertEquals('SN0001', $arrayResultante['seuNumero']);
        $this->assertEquals('IDEMP001', $arrayResultante['identificacaoBoletoEmpresa']);
        $this->assertEquals('A', $arrayResultante['codigoAceite']);
        $this->assertEquals(123.45, $arrayResultante['valor']);
        $this->assertEquals('2024-04-15T00:00:00-03:00', $arrayResultante['dataVencimento']);
        
        // Assertions for PagadorRequest
        $this->assertIsArray($arrayResultante['pagador']);
        $this->assertEquals('Pagador Teste Completo', $arrayResultante['pagador']['nome']);
        $this->assertEquals('11122233344', $arrayResultante['pagador']['numeroCpfCnpj']);
        $this->assertEquals('Rua Exemplo, 100 Sala 1', $arrayResultante['pagador']['endereco']); // Validates combined address
        $this->assertEquals('Rua Exemplo', $arrayResultante['pagador']['logradouro']);
        $this->assertEquals('Sala 1', $arrayResultante['pagador']['complemento']);


        // Assertions for BeneficiarioFinalRequest
        $this->assertIsArray($arrayResultante['beneficiarioFinal']);
        $this->assertEquals('Beneficiário Final Teste', $arrayResultante['beneficiarioFinal']['nome']);
        $this->assertEquals('55566677788', $arrayResultante['beneficiarioFinal']['numeroCpfCnpj']);

        // Assertions for MensagemInstrucao
        $this->assertIsArray($arrayResultante['mensagensInstrucao']);
        $this->assertEquals(1, $arrayResultante['mensagensInstrucao']['tipoInstrucao']);
        $this->assertEquals(['Não receber após o vencimento.', 'Sujeito a protesto.'], $arrayResultante['mensagensInstrucao']['mensagens']);

        // Assertions for optional fields
        $this->assertEquals('VALORFIXO', $arrayResultante['codigoTipoMulta']);
        $this->assertEquals(5.00, $arrayResultante['valorMulta']);
        $this->assertEquals('TAXAMENSAL', $arrayResultante['codigoTipoJurosMora']);
        $this->assertEquals(0.50, $arrayResultante['valorJurosMora']);
        $this->assertEquals('VALORFIXODATAINFORMADA', $arrayResultante['codigoTipoDesconto']);
        $this->assertEquals(10.00, $arrayResultante['valorDesconto']);
        $this->assertEquals(1, $arrayResultante['numeroParcela']);
        $this->assertEquals(10, $arrayResultante['quantidadeDiasProtesto']);
        $this->assertEquals(20, $arrayResultante['quantidadeDiasNegativacao']);
        $this->assertEquals("true", $arrayResultante['gerarPdf']);
        
        // Check that null values are filtered out (if $identificacaoBoletoEmpresa was null, it should not be in array)
        // For example, if we set identificacaoBoletoEmpresa to null:
        // $boletoRequest->identificacaoBoletoEmpresa = null;
        // $arrayResultante = $boletoRequest->toArray();
        // $this->assertArrayNotHasKey('identificacaoBoletoEmpresa', $arrayResultante);
        // However, in BoletoRequest, it's initialized to null but can be an empty string, which is valid.
        // We'll test with an empty string for one field.
        $boletoRequest->identificacaoBoletoEmpresa = "";
        $arrayResultante = $boletoRequest->toArray();
        $this->assertArrayHasKey('identificacaoBoletoEmpresa', $arrayResultante);
        $this->assertSame("", $arrayResultante['identificacaoBoletoEmpresa']);


    }
}
