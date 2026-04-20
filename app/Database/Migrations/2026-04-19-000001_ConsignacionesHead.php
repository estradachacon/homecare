<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesHead extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'auto_increment' => true],
            'numero'           => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => false],
            'vendedor_id'      => ['type' => 'INT', 'null' => false],
            'nombre'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'concepto'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'fecha'            => ['type' => 'DATE', 'null' => false],
            'hora'             => ['type' => 'TIME', 'null' => true],
            'fecha_generacion' => ['type' => 'DATETIME', 'null' => false],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'observaciones'    => ['type' => 'TEXT', 'null' => true],
            'estado'           => [
                'type'       => 'ENUM',
                'constraint' => ['abierta', 'cerrada', 'anulada'],
                'default'    => 'abierta',
            ],
            'anulada'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'anulada_por'      => ['type' => 'INT', 'null' => true],
            'fecha_anulacion'  => ['type' => 'DATETIME', 'null' => true],
            'created_by'       => ['type' => 'INT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero');
        $this->forge->createTable('consignaciones_head');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_head');
    }
}
