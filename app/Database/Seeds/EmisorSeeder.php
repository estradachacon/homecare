<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmisorSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nit' => '06141306691010',
                'nrc' => '746193',
                'nombre' => 'Hugo Filiberto Muñoz Gonzalez',
                'cod_actividad' => '46595',
                'desc_actividad' => 'Venta al por mayor de equipamiento para uso médico, odontológico, veterinario y servicios conexos',
                'nombre_comercial' => 'Hugo Filiberto Muñoz Gonzalez',
                'tipo_establecimiento' => 'CASA MATRIZ',
                'telefono' => '503 22044800',
                'correo' => 'suplidoresdiversos@hotmail.com',
                'cod_estable_mh' => 'M001',
                'cod_estable' => 'M001',
                'cod_punto_venta_mh' => 'P001',
                'cod_punto_venta' => 'P001',
                'departamento' => '06',
                'municipio' => '14',
                'complemento' => 'San Salvador',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('emisor')->insertBatch($data);
    }
}