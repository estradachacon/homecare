<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentoOrigenToContAsientosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_asientos_head', [
            'documento_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'after'      => 'referencia',
            ],
            'documento_id' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
                'after'   => 'documento_tipo',
            ],
            'reversa_de' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
                'after'   => 'documento_id',
            ],
        ]);

        $this->db->query('ALTER TABLE cont_asientos_head ADD INDEX idx_documento (documento_tipo, documento_id)');
        $this->db->query('ALTER TABLE cont_asientos_head ADD INDEX idx_reversa_de (reversa_de)');
    }

    public function down()
    {
        $this->forge->dropColumn('cont_asientos_head', ['documento_tipo', 'documento_id', 'reversa_de']);
    }
}
