<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComprasHead extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],

            'numero_control' => ['type' => 'VARCHAR', 'constraint' => 50],
            'codigo_generacion' => ['type' => 'VARCHAR', 'constraint' => 100],
            'fecha_emision' => ['type' => 'DATE', 'null' => true],
            'sello_recibido' => ['type' => 'TEXT', 'null' => true],

            // Relación
            'proveedor_id' => ['type' => 'INT', 'null' => true],

            // Totales
            'total_gravada' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'sub_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_iva' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'monto_total_operacion' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_pagar' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],

            // Condiciones
            'condicion_operacion' => ['type' => 'INT', 'default' => 1],
            'plazo_credito' => ['type' => 'INT', 'null' => true],

            // Impuestos
            'iva_rete1' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],

            // Estado
            'saldo' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'anulada' => ['type' => 'TINYINT', 'default' => 0],
            'anulada_por' => ['type' => 'INT', 'null' => true],
            'fecha_anulacion' => ['type' => 'DATETIME', 'null' => true],

            // Relación NC
            'codigo_generacion_relacionado' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],

            // Timestamps
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero_control');

        $this->forge->createTable('compras_head');
    }

    public function down()
    {
        $this->forge->dropTable('compras_head');
    }
}