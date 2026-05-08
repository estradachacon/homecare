<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCuentasVentaToContConfiguracion extends Migration
{
    public function up()
    {
        // IVA Débito Fiscal: pasivo que representa el IVA cobrado al cliente
        // y que debemos liquidar mensualmente a Hacienda.
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_iva_debito_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'cuenta_ventas_id',
                'comment'    => 'IVA Débito Fiscal (pasivo — ej: 21101)',
            ],
        ]);

        // Retención 1% por Cobrar: activo que representa el 1 % retenido
        // por clientes CCF/FAC, recuperable vía liquidación IVA mensual.
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_retencion_cobrar_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'cuenta_iva_debito_id',
                'comment'    => 'Retención IVA 1% por Cobrar (activo — ej: 11106)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_configuracion', 'cuenta_iva_debito_id');
        $this->forge->dropColumn('cont_configuracion', 'cuenta_retencion_cobrar_id');
    }
}
