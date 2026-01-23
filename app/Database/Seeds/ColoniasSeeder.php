<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ColoniasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id' => 1, 'nombre' => 'San Salvador'],
            ['id' => 2, 'nombre' => 'La Libertad'],
            ['id' => 3, 'nombre' => 'Santa Ana'],
        ];

        $this->db->table('departamentos')->insertBatch($data);
    }
}
