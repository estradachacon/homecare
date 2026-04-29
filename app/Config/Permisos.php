<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permisos extends BaseConfig
{
    public array $modulos = [
        'Facturación' => [
            'emitir_dte',
        ],

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
            'editar_vendedor_en_detalle'
        ],

        'Consignaciones' => [
            'ver_consignaciones',
            'crear_consignaciones',
            'cerrar_consignaciones',
            'anular_consignaciones',
            'aprobar_consignaciones',
            'gestionar_lotes_consignaciones',
            'ver_precios_consignaciones',
            'gestionar_precios_consignaciones',
        ],
        
        'Cuentas por cobrar' => [
            'ingresar_pagos',
            'ver_pagos',
            'crear_pagos',
            'anular_pagos',
            'ver_quedans',
            'crear_quedans',
            'anular_quedans'
        ],

        'Finanzas' => [
            'ver_transacciones',
            'ver_cuentas',
            'crear_cuenta',
            'registrar_gasto',
            'registrar_transferencia',
        ],

        'Inventario' => [
            'ver_inventario',
            'ver_proveedores',
            'crear_proveedor',
            'editar_proveedor',
            'eliminar_proveedor',
            'ver_compras',
            'cargar_compras_json',
            'ingresar_compras',
            'ver_pagos_a_compras',
            'registrar_pagos_a_compras',
        ],

        'Comisiones' => [
            'ver_comisiones',
            'generar_comisiones',
            'configurar_comisiones',
            'ver_reportes_comisiones',
        ],

        'Reportes' => [
            'ver_reportes',
        ],

        'Notificaciones' => [
            'ver_notificacion_factura_anulada',
            'vencimiento_de_quedans',
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

        'Contabilidad' => [
            'ver_contabilidad',
            'ver_plan_cuentas',
            'crear_cuenta_contable',
            'editar_cuenta_contable',
            'eliminar_cuenta_contable',
            'ver_periodos_contables',
            'crear_periodo_contable',
            'cerrar_periodo_contable',
            'ver_asientos',
            'crear_asiento',
            'aprobar_asiento',
            'anular_asiento',
            'ver_listados_contables',
            'ver_reportes_contables',
            'ejecutar_cierre_mes',
            'ejecutar_cierre_anual',
            'ver_mantenimientos_contables',
            'configurar_contabilidad',
        ],
    ];
}
