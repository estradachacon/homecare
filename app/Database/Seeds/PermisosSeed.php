<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermisosSeed extends Seeder
{
    public function run()
    {
        // ID DEL ROL ADMINISTRADOR
        $adminRoleId = 1;

        $permisos = [

            // ===== FINANZAS =====
            'ver_transacciones',
            'ver_cajas',
            'crear_caja',
            'editar_caja',
            'eliminar_caja',
            'ver_cuentas',
            'crear_cuenta',
            'ver_caja_actual',
            'registrar_gasto',

            // ===== PAQUETERÍA =====
            'crear_paquetes',
            'ver_paquetes',
            'ver_tracking',

            // ===== REMUNERACIONES =====
            'remunerar_paquetes',
            'devolver_paquetes',

            // ===== VENDEDORES =====
            'ver_vendedores',

            // ===== PUNTOS FIJOS Y RUTAS =====
            'ver_puntosfjos',
            'ver_rutas',

            // ===== SOLICITUDES =====
            'invalidar_pago',
            'invalidar_flete',

            // ===== REPORTES =====
            'ver_reportes',

            // ===== AJUSTES DEL SISTEMA =====
            'ver_configuracion',
            'ver_sucursales',
            'ver_almacenamiento',

            // ===== GESTIÓN DE USUARIOS =====
            'ver_usuarios',
            'ver_roles',
        ];

        foreach ($permisos as $accion) {

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
