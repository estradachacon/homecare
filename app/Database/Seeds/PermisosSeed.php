<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Permisos;

class PermisosSeed extends Seeder
{
    public function run()
    {
        $adminRoleId = 1;

        $config = new Permisos();

        foreach ($config->modulos as $modulo => $acciones) {

            foreach ($acciones as $accion) {

                $exists = $this->db->table('permisos_rol')
                    ->where('role_id', $adminRoleId)
                    ->where('nombre_accion', $accion)
                    ->get()
                    ->getRow();

                if (!$exists) {
                    $this->db->table('permisos_rol')->insert([
                        'role_id'       => $adminRoleId,
                        'nombre_accion' => $accion,
                        'habilitado'    => 1,
                    ]);
                }
            }
        }
    }
}
