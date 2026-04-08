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
            '01' => 'FAC',
            '03' => 'CCF',
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
            'FAC'   => 'Factura Electrónica',
            'CCF' => 'Comprobante de Crédito Fiscal Electrónico',
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

if (!function_exists('factura_builder')) {
    function factura_builder($data)
    {
        return [
            "identificacion" => [
                "tipoDte" => "01",
                "fecEmi" => date('Y-m-d'),
                "horEmi" => date('H:i:s'),
            ],
            "emisor" => [
                "nit" => $data['emisor']['nit'] ?? null,
                "nombre" => $data['emisor']['nombre'] ?? null,
            ],
            "receptor" => [
                "nit" => $data['receptor']['nit'] ?? null,
                "nombre" => $data['receptor']['nombre'] ?? null,
            ],
            "detalle" => build_detalle($data['items'] ?? []),
            "resumen" => [
                "totalPagar" => $data['total'] ?? 0
            ]
        ];
    }
}

if (!function_exists('ccf_builder')) {
    function ccf_builder($data)
    {
        return [
            "identificacion" => [
                "tipoDte" => "03",
                "fecEmi" => date('Y-m-d'),
                "horEmi" => date('H:i:s'),
            ],
            "emisor" => [
                "nit" => $data['emisor']['nit'] ?? null,
                "nombre" => $data['emisor']['nombre'] ?? null,
            ],
            "receptor" => [
                "nit" => $data['receptor']['nit'] ?? null,
                "nombre" => $data['receptor']['nombre'] ?? null,
                "nrc" => $data['receptor']['nrc'] ?? null,
            ],
            "detalle" => build_detalle($data['items'] ?? []),
            "resumen" => [
                "totalPagar" => $data['total'] ?? 0
            ]
        ];
    }
}

if (!function_exists('build_detalle')) {
    function build_detalle($items)
    {
        $detalle = [];

        foreach ($items as $i => $item) {
            $detalle[] = [
                "numItem" => $i + 1,
                "descripcion" => $item['descripcion'] ?? '',
                "cantidad" => $item['cantidad'] ?? 1,
                "precioUni" => $item['precio'] ?? 0,
                "ventaTotal" => ($item['cantidad'] ?? 1) * ($item['precio'] ?? 0),
            ];
        }

        return $detalle;
    }
}