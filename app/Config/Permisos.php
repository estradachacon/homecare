<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permisos extends BaseConfig
{
    public array $modulos = [

        'Ventas' => [
            'cargar_facturas',
            'ver_facturas',
            'ver_clientes',
            'crear_clientes',
            'editar_clientes',
            'eliminar_clientes',
            'anular_factura',
            'ver_tipo_venta',
            'crear_tipo_venta',
            'editar_tipo_venta',
            'eliminar_tipo_venta',
        ],

        'Vendedores' => [
            'ver_vendedores',
            'crear_vendedor',
            'editar_vendedor',
            'eliminar_vendedor',
        ],
        
        'Cuentas por cobrar' => [
            'ingresar_pagos',
        ],
        
        'Finanzas' => [
            'ver_transacciones',
            'ver_cuentas',
            'crear_cuenta',
            'registrar_gasto',
            'registrar_transferencia',
        ],

        'Reportes' => [
            'ver_reportes',
        ],

        'Ajustes del sistema' => [
            'ver_configuracion',
            'ver_sucursales',
            'ver_almacenamiento',
            'ver_bitacora',
        ],

        'Gestión de usuarios' => [
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'ver_roles',
            'editar_roles',
            'eliminar_roles',
            'crear_roles',
            'asignar_permisos',
        ],
    ];
}
