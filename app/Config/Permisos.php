<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permisos extends BaseConfig
{
    public array $modulos = [

        'Finanzas' => [
            'ver_transacciones',
            'ver_cajas',
            'crear_caja',
            'editar_caja',
            'eliminar_caja',
            'hacer_corte',
            'ver_cuentas',
            'crear_cuenta',
            'ver_historicos_de_caja',
            'registrar_gasto',
            'registrar_transferencia',
        ],

        'Paquetería' => [
            'crear_paquetes',
            'ver_paquetes',
            'ver_tracking',
            'crear_tracking',
        ],

        'Remuneraciones' => [
            'remunerar_paquetes',
            'devolver_paquetes',
        ],

        'Vendedores' => [
            'ver_vendedores',
            'crear_vendedor',
            'editar_vendedor',
            'eliminar_vendedor',
        ],

        'Puntos fijos y rutas' => [
            'ver_puntosfijos',
            'crear_puntofijo',
            'editar_puntofijo',
            'eliminar_puntofijo',
            'ver_rutas',
            'crear_ruta',
            'editar_ruta',
            'eliminar_ruta',
        ],

        'Solicitudes' => [
            'invalidar_pago',
            'invalidar_flete',
        ],

        'Reportes' => [
            'ver_reportes',
        ],

        'Ajustes del sistema' => [
            'ver_configuracion',
            'ver_sucursales',
            'ver_almacenamiento',
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
