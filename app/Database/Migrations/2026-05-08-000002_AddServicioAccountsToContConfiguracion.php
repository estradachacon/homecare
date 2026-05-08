<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServicioAccountsToContConfiguracion extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_ventas_servicio1_id' => [
                'type'    => 'INT',
                'unsigned'=> true,
                'null'    => true,
                'default' => null,
                'after'   => 'cuenta_retencion_cobrar_id',
                'comment' => 'Cuenta ingresos servicios — opción 1',
            ],
        ]);
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_ventas_servicio1_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'cuenta_ventas_servicio1_id',
            ],
        ]);
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_ventas_servicio2_id' => [
                'type'    => 'INT',
                'unsigned'=> true,
                'null'    => true,
                'default' => null,
                'after'   => 'cuenta_ventas_servicio1_label',
                'comment' => 'Cuenta ingresos servicios — opción 2',
            ],
        ]);
        $this->forge->addColumn('cont_configuracion', [
            'cuenta_ventas_servicio2_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'cuenta_ventas_servicio2_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_configuracion', 'cuenta_ventas_servicio1_id');
        $this->forge->dropColumn('cont_configuracion', 'cuenta_ventas_servicio1_label');
        $this->forge->dropColumn('cont_configuracion', 'cuenta_ventas_servicio2_id');
        $this->forge->dropColumn('cont_configuracion', 'cuenta_ventas_servicio2_label');
    }
}
