<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCaracteristicasToClientes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('clientes', [
            'gran_contribuyente' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'nrc',
            ],
            'exento_iva' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'gran_contribuyente',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('clientes', ['gran_contribuyente', 'exento_iva']);
    }
}
