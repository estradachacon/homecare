<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermisosSeed extends Seeder
{
    public function run()
    {
        // ===== PERMISOS ICONICOS =====
        $acciones = [
            // Usuarios
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',

            // Roles
            'ver_roles',
            'editar_roles',

            // Clientes
            'ver_clientes',
            'crear_clientes',
            'editar_clientes',

            // Ventas
            'crear_venta',
            'ver_ventas',
            'anular_venta',

            // Productos
            'ver_productos',
            'crear_productos',
            'editar_productos',
            'borrar_productos',

            // Reportes
            'ver_reportes',

            // Sucursales
            'ver_sucursales',
            'crear_sucursales',
            'editar_sucursales',

            // Caja
            'abrir_caja',
            'cerrar_caja',
            'ver_movimientos_caja',
        ];

        // ===== ROLES PREDEFINIDOS =====
        // Asegúrate de haber creado previamente la tabla roles con ids esperados
        // Role_id: 1 = Gerente, 2 = Supervisor, 3 = Vendedor, 4 = Contador

        $permisos = [];

        foreach ($acciones as $accion) {

            // GERENTE — tiene todo
            $permisos[] = [
                'role_id' => 1,
                'nombre_accion' => $accion,
                'habilitado' => 1
            ];

            // SUPERVISOR
            $permisos[] = [
                'role_id' => 2,
                'nombre_accion' => $accion,
                'habilitado' => in_array($accion, [
                    'ver_usuarios',
                    'ver_clientes',
                    'crear_clientes',
                    'editar_clientes',
                    'crear_venta',
                    'ver_ventas',
                    'ver_reportes',
                    'ver_productos',
                    'ver_sucursales',
                    'abrir_caja',
                    'cerrar_caja',
                    'ver_movimientos_caja'
                ]) ? 1 : 0
            ];

            // VENDEDOR
            $permisos[] = [
                'role_id' => 3,
                'nombre_accion' => $accion,
                'habilitado' => in_array($accion, [
                    'ver_clientes',
                    'crear_clientes',
                    'editar_clientes',
                    'crear_venta',
                    'ver_ventas'
                ]) ? 1 : 0
            ];

            // CONTADOR
            $permisos[] = [
                'role_id' => 4,
                'nombre_accion' => $accion,
                'habilitado' => in_array($accion, [
                    'ver_ventas',
                    'ver_reportes',
                    'ver_movimientos_caja'
                ]) ? 1 : 0
            ];
        }

        // Insertar permisos en la tabla permisos_rol
        $this->db->table('permisos_rol')->insertBatch($permisos);

        echo "Seeder de permisos ejecutado correctamente.\n";
    }
}
