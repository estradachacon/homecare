<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisiones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'vendedor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
            ],
            'total_ventas' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'total_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'porcentaje_promedio' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'pagado'],
                'default'    => 'pendiente',
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('comisiones');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones');
    }
}