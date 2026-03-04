<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCodigoGeneracionRelacionadoToFacturasHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facturas_head', [
            'codigo_generacion_relacionado' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'codigo_generacion',
            ],
        ]);

        // Índice para búsquedas rápidas
        $this->db->query('CREATE INDEX idx_codigo_generacion_relacionado 
                          ON facturas_head (codigo_generacion_relacionado)');
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', 'codigo_generacion_relacionado');
    }
}
