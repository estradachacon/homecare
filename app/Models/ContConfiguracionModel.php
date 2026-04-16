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
