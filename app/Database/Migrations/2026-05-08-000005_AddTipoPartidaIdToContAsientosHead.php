<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoPartidaIdToContAsientosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_asientos_head', [
            'tipo_partida_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'tipo',
            ],
        ]);
        $this->db->query('ALTER TABLE cont_asientos_head ADD CONSTRAINT fk_asientos_tipo_partida FOREIGN KEY (tipo_partida_id) REFERENCES cont_tipos_partida(id) ON DELETE SET NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE cont_asientos_head DROP FOREIGN KEY fk_asientos_tipo_partida');
        $this->forge->dropColumn('cont_asientos_head', 'tipo_partida_id');
    }
}
