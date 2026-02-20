<?php

if (!function_exists('dte_tipos')) {
    function dte_tipos()
    {
        return [
            '01' => 'Factura',
            '03' => 'Comprobante de crédito fiscal',
            '04' => 'Nota de remisión',
            '05' => 'Nota de crédito',
            '06' => 'Nota de débito',
            '07' => 'Comprobante de retención',
            '08' => 'Comprobante de liquidación',
            '09' => 'Documento contable de liquidación',
            '11' => 'Facturas de exportación',
            '14' => 'Factura de sujeto excluido',
            '15' => 'Comprobante de donación',
        ];
    }
}

if (!function_exists('dte_siglas')) {
    function dte_siglas()
    {
        return [
            '01' => 'FE',
            '03' => 'CCFE',
            '04' => 'NRE',
            '05' => 'NCE',
            '06' => 'NDE',
            '07' => 'CRE',
            '08' => 'CLE',
            '09' => 'DCLE',
            '11' => 'FEXE',
            '14' => 'FSEE',
            '15' => 'CDE',
        ];
    }
}

if (!function_exists('dte_descripciones')) {
    function dte_descripciones()
    {
        return [
            'FE'   => 'Factura Electrónica',
            'CCFE' => 'Comprobante de Crédito Fiscal Electrónico',
            'NCE'  => 'Nota de Crédito Electrónica',
            'NDE'  => 'Nota de Débito Electrónica',
            'NRE'  => 'Nota de Remisión Electrónica',
            'CRE'  => 'Comprobante de Retención Electrónico',
            'CLE'  => 'Comprobante de Liquidación Electrónico',
            'DCLE' => 'Documento Contable de Liquidación Electrónico',
            'FSEE' => 'Factura Sujeto Excluido Electrónico',
            'CDE'  => 'Comprobante de Donación Electrónico',
            'FEXE' => 'Factura de Exportación Electrónica',
        ];
    }
}