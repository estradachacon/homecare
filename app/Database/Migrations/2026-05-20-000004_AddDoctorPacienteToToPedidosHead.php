<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDoctorPacienteToToPedidosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos_head', [
            'doctor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'consignacion_id',
            ],
            'paciente_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'doctor_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos_head', ['doctor_id', 'paciente_id']);
    }
}
