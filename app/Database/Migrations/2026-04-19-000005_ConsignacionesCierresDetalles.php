<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesCierresDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'auto_increment' => true],
            'cierre_id'             => ['type' => 'INT', 'null' => false],
            'detalle_id'            => ['type' => 'INT', 'null' => false],
            'producto_id'           => ['type' => 'INT', 'null' => false],
            'cantidad_facturada'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'cantidad_devuelta'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'cantidad_stock_vendedor' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'doc_devolucion'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'foto_devolucion'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'comentario_devolucion' => ['type' => 'TEXT', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_cierres_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_cierres_detalles');
    }
}
