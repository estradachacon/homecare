<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FacturasHead extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // IDENTIFICACION
            'ambiente' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
            ],
            'tipo_dte' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
            ],
            'numero_control' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'codigo_generacion' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'fecha_emision' => [
                'type' => 'DATE',
            ],
            'hora_emision' => [
                'type' => 'TIME',
            ],
            'tipo_moneda' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'sello_recibido' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // RECEPTOR
            'receptor_id' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],

            // RESUMEN
            'total_gravada' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'sub_total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'total_iva' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'monto_total_operacion' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'total_pagar' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'condicion_operacion' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'vendedor_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            // FIRMA
            'firma_electronica' => [
                'type' => 'LONGTEXT',
            ],

            'created_at DATETIME default CURRENT_TIMESTAMP',
            'updated_at DATETIME default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',

        ]);

        $this->forge->addKey('id', true);

        // Índices importantes
        $this->forge->addUniqueKey('numero_control');
        $this->forge->addUniqueKey('codigo_generacion');
        $this->forge->addKey('fecha_emision');
        $this->forge->addKey('receptor_id');

        $this->forge->createTable('facturas_head');
    }

    public function down()
    {
        $this->forge->dropTable('facturas_head');
    }
}
