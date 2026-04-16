<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContConfiguracion extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'auto_increment' => true],
            'cuenta_caja_id'        => ['type' => 'INT', 'null' => true],
            'cuenta_banco_id'       => ['type' => 'INT', 'null' => true],
            'cuenta_cxc_id'         => ['type' => 'INT', 'null' => true],
            'cuenta_cxp_id'         => ['type' => 'INT', 'null' => true],
            'cuenta_inventario_id'  => ['type' => 'INT', 'null' => true],
            'cuenta_ventas_id'      => ['type' => 'INT', 'null' => true],
            'cuenta_costos_id'      => ['type' => 'INT', 'null' => true],
            'cuenta_gastos_admin_id'=> ['type' => 'INT', 'null' => true],
            'cuenta_gastos_venta_id'=> ['type' => 'INT', 'null' => true],
            'cuenta_resultado_id'   => ['type' => 'INT', 'null' => true],
            'cuenta_capital_id'     => ['type' => 'INT', 'null' => true],
            'moneda'                => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'USD'],
            'digitos_decimales'     => ['type' => 'TINYINT', 'default' => 2],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cont_configuracion');
    }

    public function down()
    {
        $this->forge->dropTable('cont_configuracion');
    }
}
