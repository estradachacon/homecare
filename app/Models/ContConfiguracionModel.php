<?php

namespace App\Models;

use CodeIgniter\Model;

class ContConfiguracionModel extends Model
{
    protected $table         = 'cont_configuracion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'cuenta_caja_id', 'cuenta_banco_id', 'cuenta_cxc_id', 'cuenta_cxp_id',
        'cuenta_inventario_id', 'cuenta_ventas_id', 'cuenta_costos_id',
        'cuenta_gastos_admin_id', 'cuenta_gastos_venta_id',
        'cuenta_resultado_id', 'cuenta_capital_id',
        // Cuentas de ventas automáticas (CCF / FAC) — migración 2026-05-08-000001
        'cuenta_iva_debito_id',        // IVA Débito Fiscal (pasivo)
        'cuenta_retencion_cobrar_id',  // Retención IVA 1% por Cobrar (activo)
        // Cuentas de servicios — migración 2026-05-08-000002
        'cuenta_ventas_servicio1_id', 'cuenta_ventas_servicio1_label',
        'cuenta_ventas_servicio2_id', 'cuenta_ventas_servicio2_label',
        'moneda', 'digitos_decimales',
    ];

    public function getConfig()
    {
        return $this->first() ?? (object)[];
    }

    public function guardar(array $data): bool
    {
        $existing = $this->first();
        if ($existing) {
            return $this->update($existing->id, $data);
        }
        return (bool)$this->insert($data);
    }
}
