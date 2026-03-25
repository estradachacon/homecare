<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class DteContingencia extends BaseConfig
{
    // CAT-022 Tipo de documento de identificación del Receptor		
    public $tipoDoc = [
        '01' => 'Factura Electrónico',
        '03' => 'Comprobante de Crédito Fiscal Electrónico',
        '04' => 'Nota de Remisión Electrónica',
        '05' => 'Nota de Crédito Electrónica',
        '06' => 'Nota de Débito Electrónica',
        '11' => 'Factura de Exportación Electrónica',
        '14' => 'Factura de Sujeto Excluido Electrónica'
    ];
}
