<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TareasSistemaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre' => 'notificacion_vencimiento_quedans',
                'ultima_ejecucion' => null
            ]
        ];

        $this->db->table('tareas_sistema')->insertBatch($data);
    }
}