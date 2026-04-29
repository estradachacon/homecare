<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CatalogosHaciendaSeeder extends Seeder
{
    public function run()
    {
        $departamentos = [
            ['codigo' => '00', 'nombre' => 'Otro (Para extranjeros)'],
            ['codigo' => '01', 'nombre' => 'Ahuachapán'],
            ['codigo' => '02', 'nombre' => 'Santa Ana'],
            ['codigo' => '03', 'nombre' => 'Sonsonate'],
            ['codigo' => '04', 'nombre' => 'Chalatenango'],
            ['codigo' => '05', 'nombre' => 'La Libertad'],
            ['codigo' => '06', 'nombre' => 'San Salvador'],
            ['codigo' => '07', 'nombre' => 'Cuscatlán'],
            ['codigo' => '08', 'nombre' => 'La Paz'],
            ['codigo' => '09', 'nombre' => 'Cabañas'],
            ['codigo' => '10', 'nombre' => 'San Vicente'],
            ['codigo' => '11', 'nombre' => 'Usulután'],
            ['codigo' => '12', 'nombre' => 'San Miguel'],
            ['codigo' => '13', 'nombre' => 'Morazán'],
            ['codigo' => '14', 'nombre' => 'La Unión'],
        ];

        $municipios = [
            ['departamento_codigo' => '00', 'codigo' => '00', 'nombre' => 'Otro (Para extranjeros)'],

            ['departamento_codigo' => '01', 'codigo' => '13', 'nombre' => 'AHUACHAPAN NORTE'],
            ['departamento_codigo' => '01', 'codigo' => '14', 'nombre' => 'AHUACHAPAN CENTRO'],
            ['departamento_codigo' => '01', 'codigo' => '15', 'nombre' => 'AHUACHAPAN SUR'],

            ['departamento_codigo' => '02', 'codigo' => '14', 'nombre' => 'SANTA ANA NORTE'],
            ['departamento_codigo' => '02', 'codigo' => '15', 'nombre' => 'SANTA ANA CENTRO'],
            ['departamento_codigo' => '02', 'codigo' => '16', 'nombre' => 'SANTA ANA ESTE'],
            ['departamento_codigo' => '02', 'codigo' => '17', 'nombre' => 'SANTA ANA OESTE'],

            ['departamento_codigo' => '03', 'codigo' => '17', 'nombre' => 'SONSONATE NORTE'],
            ['departamento_codigo' => '03', 'codigo' => '18', 'nombre' => 'SONSONATE CENTRO'],
            ['departamento_codigo' => '03', 'codigo' => '19', 'nombre' => 'SONSONATE ESTE'],
            ['departamento_codigo' => '03', 'codigo' => '20', 'nombre' => 'SONSONATE OESTE'],

            ['departamento_codigo' => '04', 'codigo' => '34', 'nombre' => 'CHALATENANGO NORTE'],
            ['departamento_codigo' => '04', 'codigo' => '35', 'nombre' => 'CHALATENANGO CENTRO'],
            ['departamento_codigo' => '04', 'codigo' => '36', 'nombre' => 'CHALATENANGO SUR'],

            ['departamento_codigo' => '05', 'codigo' => '23', 'nombre' => 'LA LIBERTAD NORTE'],
            ['departamento_codigo' => '05', 'codigo' => '24', 'nombre' => 'LA LIBERTAD CENTRO'],
            ['departamento_codigo' => '05', 'codigo' => '25', 'nombre' => 'LA LIBERTAD OESTE'],
            ['departamento_codigo' => '05', 'codigo' => '26', 'nombre' => 'LA LIBERTAD ESTE'],
            ['departamento_codigo' => '05', 'codigo' => '27', 'nombre' => 'LA LIBERTAD COSTA'],
            ['departamento_codigo' => '05', 'codigo' => '28', 'nombre' => 'LA LIBERTAD SUR'],

            ['departamento_codigo' => '06', 'codigo' => '20', 'nombre' => 'SAN SALVADOR NORTE'],
            ['departamento_codigo' => '06', 'codigo' => '21', 'nombre' => 'SAN SALVADOR OESTE'],
            ['departamento_codigo' => '06', 'codigo' => '22', 'nombre' => 'SAN SALVADOR ESTE'],
            ['departamento_codigo' => '06', 'codigo' => '23', 'nombre' => 'SAN SALVADOR CENTRO'],
            ['departamento_codigo' => '06', 'codigo' => '24', 'nombre' => 'SAN SALVADOR SUR'],

            ['departamento_codigo' => '07', 'codigo' => '17', 'nombre' => 'CUSCATLAN NORTE'],
            ['departamento_codigo' => '07', 'codigo' => '18', 'nombre' => 'CUSCATLAN SUR'],

            ['departamento_codigo' => '08', 'codigo' => '23', 'nombre' => 'LA PAZ OESTE'],
            ['departamento_codigo' => '08', 'codigo' => '24', 'nombre' => 'LA PAZ CENTRO'],
            ['departamento_codigo' => '08', 'codigo' => '25', 'nombre' => 'LA PAZ ESTE'],

            ['departamento_codigo' => '09', 'codigo' => '10', 'nombre' => 'CABAÑAS OESTE'],
            ['departamento_codigo' => '09', 'codigo' => '11', 'nombre' => 'CABAÑAS ESTE'],

            ['departamento_codigo' => '10', 'codigo' => '14', 'nombre' => 'SAN VICENTE NORTE'],
            ['departamento_codigo' => '10', 'codigo' => '15', 'nombre' => 'SAN VICENTE SUR'],

            ['departamento_codigo' => '11', 'codigo' => '24', 'nombre' => 'USULUTAN NORTE'],
            ['departamento_codigo' => '11', 'codigo' => '25', 'nombre' => 'USULUTAN ESTE'],
            ['departamento_codigo' => '11', 'codigo' => '26', 'nombre' => 'USULUTAN OESTE'],

            ['departamento_codigo' => '12', 'codigo' => '21', 'nombre' => 'SAN MIGUEL NORTE'],
            ['departamento_codigo' => '12', 'codigo' => '22', 'nombre' => 'SAN MIGUEL CENTRO'],
            ['departamento_codigo' => '12', 'codigo' => '23', 'nombre' => 'SAN MIGUEL OESTE'],

            ['departamento_codigo' => '13', 'codigo' => '27', 'nombre' => 'MORAZAN NORTE'],
            ['departamento_codigo' => '13', 'codigo' => '28', 'nombre' => 'MORAZAN SUR'],

            ['departamento_codigo' => '14', 'codigo' => '19', 'nombre' => 'LA UNION NORTE'],
            ['departamento_codigo' => '14', 'codigo' => '20', 'nombre' => 'LA UNION SUR'],
        ];

            $this->db->disableForeignKeyChecks();

            $this->db->table('hacienda_municipios')->truncate();
            $this->db->table('hacienda_departamentos')->truncate();

            $this->db->enableForeignKeyChecks();

            $this->db->table('hacienda_departamentos')->insertBatch($departamentos);
            $this->db->table('hacienda_municipios')->insertBatch($municipios);
    }
}