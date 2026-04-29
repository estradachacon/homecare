<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContPlanCuentasSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $cuentas = [
            // ===================== ACTIVO =====================
            ['codigo' => '1',           'nombre' => 'ACTIVO',                                         'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11',          'nombre' => 'ACTIVOS CORRIENTES',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '1101',        'nombre' => 'EFECTIVO Y EQUIVALENTES',                         'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110101',      'nombre' => 'CAJA GENERAL',                                    'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11010101',    'nombre' => 'CAJA GRANDE',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11010102',    'nombre' => 'CAJA CHICA FONDO FIJO',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11010103',    'nombre' => 'CAJA CHICA FONDO CIRCULANTE',                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110102',      'nombre' => 'BANCOS',                                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11010201',    'nombre' => 'BANCO AGRICOLA CTA N°',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11010202',    'nombre' => 'BANCO CUSCATLAN CTA N°',                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1102',        'nombre' => 'CUENTAS Y DOCUMENTOS POR COBRAR',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110201',      'nombre' => 'CLIENTES LOCALES',                                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '1102010001',  'nombre' => 'DIAGNOSTICO',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '1102010002',  'nombre' => 'CLIENTE 2',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110202',      'nombre' => 'CLIENTES EXTERIOR',                               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '1103',        'nombre' => 'DEUDORES Y DOCUMENTOS POR COBRAR',                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110301',      'nombre' => 'INTERESES POR COBRAR',                            'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110302',      'nombre' => 'VALOR POR RESCATE DE SEGUROS',                    'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110303',      'nombre' => 'CHEQUES RECHAZADOS',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110304',      'nombre' => 'PAGOS POR CUENTA AJENA',                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110305',      'nombre' => 'DEPOSITOS DADOS EN GARANTIA',                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110306',      'nombre' => 'ASUNTOS PENDIENTES POR LIQUIDAR',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110307',      'nombre' => 'ANTICIPO A PROVEEDORES',                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11030701',    'nombre' => 'SUPLIDORES DIVERSOS S.A. DE C.V.',                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11030702',    'nombre' => 'EVERGRAND EL SALVADOR S.A. DE CV.',               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1104',        'nombre' => 'PRESTAMOS Y ANTICIPOS A EMPLEADOS',               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110401',      'nombre' => 'PRESTAMOS A EMPLEADOS',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '1104010001',  'nombre' => 'EMPLEADOS 1',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '1104010002',  'nombre' => 'EMPLEADOS 2',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110402',      'nombre' => 'ANTICIPOS A EMPLEADOS',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '1104020001',  'nombre' => 'EMPLEADOS 1',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '1104020002',  'nombre' => 'EMPLEADOS 2',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110403',      'nombre' => 'ANTICIPOS A GASTOS POR LIQUIDAR',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1105',        'nombre' => 'CUENTAS POR COBRAR EN SUSPENSO',                  'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110501',      'nombre' => 'CUENTAS Y DOCUMENTOS POR COBRAR',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110502',      'nombre' => 'DEUDORES POR SERVICIOS PRESTADOS',                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110503',      'nombre' => 'PRESTAMOS Y ANTICIPOS A EMPLEADOS',               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1106',        'nombre' => 'ESTIMACION PARA CUENTAS INCOBRABLES (CR)',        'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110601',      'nombre' => 'CUENTAS Y DOCUMENTOS POR COBRAR',                 'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110602',      'nombre' => 'DEUDORES POR SERVICIOS PRESTADOS',                'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110603',      'nombre' => 'PRESTAMOS Y ANTICIPOS A EMPLEADOS',               'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1107',        'nombre' => 'IMPUESTOS POR COBRAR',                            'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110701',      'nombre' => 'CREDITO FISCAL IVA',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11070101',    'nombre' => 'IVA CREDITO FISCAL',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070102',    'nombre' => 'IVA PAGO A CUENTA',                               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070103',    'nombre' => 'IVA PERCIBIDO',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070104',    'nombre' => 'IVA RETENIDO',                                    'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070105',    'nombre' => 'IVA PROPORCIONAL',                                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110702',      'nombre' => 'PAGO A CUENTA',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '11070201',    'nombre' => 'PAGO A CUENTA DE EJERCICIOS ANTERIORES',          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070202',    'nombre' => 'PAGO A CUENTA DE EJERCICIOS CORRIENTE',           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070203',    'nombre' => 'ISR RETENIDO SOBRE SUELDOS',                      'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070204',    'nombre' => 'ISR RETENIDO SOBRE UTILIDADES',                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070205',    'nombre' => 'ISR RETENIDO SOBRE INTERESES',                    'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070206',    'nombre' => 'ISR SOBRE ALQUILERES',                            'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070207',    'nombre' => 'ISR SOBRE INVERSIONES',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070208',    'nombre' => 'ISR SOBRE SERVICIOS',                             'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '11070209',    'nombre' => 'ISR OTROS',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110703',      'nombre' => 'ACTIVO POR IMPUESTO DIFERIDO',                    'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '1108',        'nombre' => 'INVERSIONES TEMPORALES',                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110801',      'nombre' => 'ACCIONES EN OTRAS EMPRESAS',                      'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110802',      'nombre' => 'BONOS DEL ESTADO',                                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110803',      'nombre' => 'CERTIFICADOS DE CREDITO FISCAL',                  'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1109',        'nombre' => 'INVENTARIOS',                                     'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '110901',      'nombre' => 'INVENTARIO DISPONIBLE',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110902',      'nombre' => 'INVENTARIO EN TRANSITO',                          'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110903',      'nombre' => 'INVENTARIO EN CONSIGNACION',                      'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '110904',      'nombre' => 'AVERIAS Y OBSOLESCENCIA',                         'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1110',        'nombre' => 'GASTOS PAGADOS POR ANTICIPADO',                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            // --- Activos No Corrientes ---
            ['codigo' => '12',          'nombre' => 'ACTIVOS NO CORRIENTES',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '1201',        'nombre' => 'BIENES NO DEPRECIALES',                           'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '120101',      'nombre' => 'TERRENOS',                                        'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '12010101',    'nombre' => 'TERRENO 1 (BODEGA DE MEJICANOS)',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '12010102',    'nombre' => 'TERRENO 2 (BODEGA DE MEJICANOS)',                 'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120102',      'nombre' => 'OBRAS EN PROCESO',                                'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '12010201',    'nombre' => 'OBRAS EN PROCESO 1',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '12010202',    'nombre' => 'OBRAS EN PROCESO 2',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1202',        'nombre' => 'BIENES DEPRECIABLES',                             'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '120201',      'nombre' => 'EDIFICIOS',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '12020101',    'nombre' => 'CONSTRUCION EN BODEGA DE MEJICANOS',              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120202',      'nombre' => 'MOBILIARIO Y EQUIPO DE OFICINA',                  'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120203',      'nombre' => 'MAQUINARIA, EQUIPOS Y HERRAMIENTAS',              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120204',      'nombre' => 'VEHICULOS',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '12020401',    'nombre' => 'CAMION FUSO 1',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '12020402',    'nombre' => 'CAMION FUSO 2',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120205',      'nombre' => 'INSTALACIONES',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1203',        'nombre' => 'DEPRECIACION ACUMULADA (CR)',                     'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '120301',      'nombre' => 'EDIFICIOS',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120302',      'nombre' => 'MOBILIARIO Y EQUIPO DE OFICINA',                  'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120303',      'nombre' => 'MAQUINARIA, EQUIPOS Y HERRAMIENTAS',              'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120304',      'nombre' => 'VEHICULOS',                                       'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120305',      'nombre' => 'INSTALACIONES',                                   'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1204',        'nombre' => 'BIENES INTANGIBLES',                              'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '120401',      'nombre' => 'GASTOS DE INVESTIGACION Y DESARROLLO',            'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120402',      'nombre' => 'PATENTES Y MARCAS',                               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120403',      'nombre' => 'NOMBRES COMERCIALES',                             'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120404',      'nombre' => 'PLUSVALIA MERCANTIL',                             'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120405',      'nombre' => 'DERECHOS DE LLAVE',                               'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120406',      'nombre' => 'LICENCIAS Y FRANQUICIAS',                         'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1205',        'nombre' => 'AMORTIZACION ACUMULADA (CR)',                     'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '120501',      'nombre' => 'AMORTIZACION DE GASTOS DE INVESTIGACION Y DESARROLLO', 'tipo' => 'ACTIVO',  'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120502',      'nombre' => 'AMORTIZACION DE PATENTES Y MARCAS',               'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120503',      'nombre' => 'AMORTINAZION DE NOMBRES COMERCIALES',             'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120504',      'nombre' => 'AMORTIZACION DE PLUSVALIA MERCANTIL',             'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120505',      'nombre' => 'AMORTIZACION DE DERECHOS DE LLAVE',               'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '120506',      'nombre' => 'AMORTIZACION DE LICENCIAS Y FRANQUICIAS',         'tipo' => 'ACTIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '1206',        'nombre' => 'ARRENDAMIENTO FINANCIERO',                        'tipo' => 'ACTIVO',        'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            // ===================== PASIVO =====================
            ['codigo' => '2',           'nombre' => 'PASIVO',                                          'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21',          'nombre' => 'PASIVOS CORRIENTES',                              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '2101',        'nombre' => 'CUENTAS Y DOCUMENTOS POR PAGAR',                  'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210101',      'nombre' => 'PROVEEDORES LOCALES',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2101010001',  'nombre' => 'PROVEEDOR 1',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2101010002',  'nombre' => 'PROVEEDOR 2',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210102',      'nombre' => 'PROVEEDORES DEL EXTERIOR',                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2101020001',  'nombre' => 'PROVEEDOR 1',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2101020002',  'nombre' => 'PROVEEDOR 2',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2102',        'nombre' => 'ACREEDORES POR SERVICIOS RECIBIDOS',              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210201',      'nombre' => 'INTERESES POR PAGAR',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210202',      'nombre' => 'DOCUMENTOS ENDOSADOS Y DESCONTADOS',              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210203',      'nombre' => 'COBROS POR CUENTA AJENA',                         'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210204',      'nombre' => 'DEPOSITOS RECIBIDOS DE CLIENTES',                 'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210205',      'nombre' => 'CUENTAS PENDIENTES POR LIQUIDAR',                 'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210206',      'nombre' => 'SERVICIOS CORRIENTES DIVERSOS',                   'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2103',        'nombre' => 'PRESTAMOS A CORTO PLAZO',                         'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210301',      'nombre' => 'SOBREGIROS BANCARIOS',                            'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21030101',    'nombre' => 'BANCO HIPOTECARIO',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103010101',  'nombre' => 'SOBREGIRO 1',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103010102',  'nombre' => 'SOBREGIRO 2',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21030102',    'nombre' => 'BANCO CREDOMATIC',                                'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103010201',  'nombre' => 'SOBREGIRO 1',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103010202',  'nombre' => 'SOBREGIRO 2',                                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210302',      'nombre' => 'PORCION CORRIENTE DE PRESTAMOS A LARGO PLAZO',    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21030201',    'nombre' => 'BANCO HIPOTECARIO',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103020101',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103020102',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21030202',    'nombre' => 'BANCO CREDOMATIC',                                'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103020201',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103020202',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210303',      'nombre' => 'PRESTAMOS BANCARIOS',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21030301',    'nombre' => 'BANCO HIPOTECARIO',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103030101',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103030102',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21030302',    'nombre' => 'BANCO CREDOMATIC',                                'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2103030201',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2103030202',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210304',      'nombre' => 'PRESTAMOS RECIBIDOS DE PARTICULARES',             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21030401',    'nombre' => 'PARTICULAR 1',                                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21030402',    'nombre' => 'PARTICULAR 2',                                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210305',      'nombre' => 'OTROS PRESTAMOS A CORTO PLAZO',                   'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21030501',    'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21030502',    'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2104',        'nombre' => 'IMPUESTOS POR PAGAR',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210401',      'nombre' => 'DEBITO FISCAL IVA',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21040101',    'nombre' => 'IVA DEBITO FISCAL',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21040102',    'nombre' => 'RETENCIONES IVA A PEQUEÑOS',                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '21040103',    'nombre' => 'PERCEPCION DE IVA A PEQUEÑOS',                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210402',      'nombre' => 'IMPUESTOS MUNICIPALES',                           'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210403',      'nombre' => 'IMPUESTO SOBRE LA RENTA',                         'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210404',      'nombre' => 'PROVISION DE PAGO A CUENTA',                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210405',      'nombre' => 'PASIVO POR IMPUESTOS DIFERIDOS',                  'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '21040501',    'nombre' => 'DIFERENCIAS TEMPORALES IMPONIBLES',               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2105',        'nombre' => 'RETENCIONES LABORALES',                           'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210501',      'nombre' => 'ISSS RETENIDO A EMPLEADOS',                       'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210502',      'nombre' => 'AFP RETENIDO A EMPLEADOS',                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210503',      'nombre' => 'ISR RETENIDO A EMPLEADOS',                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210504',      'nombre' => 'VIALIDAD',                                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210505',      'nombre' => 'FONDO SOCIAL',                                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210506',      'nombre' => 'CUOTA DE PRESTAMOS EXTERNOS',                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210507',      'nombre' => 'EMBARGOS JUDICIALES',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210508',      'nombre' => 'CUOTA SINDICAL',                                  'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210509',      'nombre' => 'OTRAS RETENCIONES',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2106',        'nombre' => 'PROVISIONES PATRONALES',                          'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210601',      'nombre' => 'SUELDOS POR PAGAR',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210602',      'nombre' => 'COMISIONES POR PAGAR',                            'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210603',      'nombre' => 'BONIFICACIONES POR PAGAR',                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210604',      'nombre' => 'VACACIONES POR PAGAR',                            'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210605',      'nombre' => 'AGUINALDOS POR PAGAR',                            'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210606',      'nombre' => 'INDEMNIZACIONES POR PAGAR',                       'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210607',      'nombre' => 'CUOTA PATRONAL ISSS',                             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210608',      'nombre' => 'CUOTA PATRONAL AFP',                              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210609',      'nombre' => 'CUOTA PATRONAL INSAFORP',                         'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210610',      'nombre' => 'BONOS A EMPLEADOS',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210611',      'nombre' => 'PARTICIPACIONES A EMPLEADOS',                     'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210612',      'nombre' => 'OTRAS PROVISIONES',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '2107',        'nombre' => 'SERVICIOS COBRADOS POR ANTICIPADOS',              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '210701',      'nombre' => 'INTERESES COBRADOS POR ANTICIPADOS',              'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210702',      'nombre' => 'COMISIONES COBRADAS POR ANTICIPADAS',             'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '210703',      'nombre' => 'OTROS PRODUCTOS POR LIQUIDAR',                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            // --- Pasivos No Corrientes ---
            ['codigo' => '22',          'nombre' => 'PASIVOS NO CORRIENTES',                           'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '2201',        'nombre' => 'PRESTAMOS A LARGO PLAZOS',                        'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '220101',      'nombre' => 'PRESTAMO BANCARIOS A LARGO PLAZO',                'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '22010101',    'nombre' => 'BANCO HIPOTECARIO',                               'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2201010101',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2201010102',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '22010102',    'nombre' => 'BANCO CREDOMATIC',                                'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '2201010201',  'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '2201010202',  'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 6, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '220102',      'nombre' => 'PRESTAMOS DE PARTICULARES A LARGO PLAZO',         'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '22010201',    'nombre' => 'PARTICULAR 1',                                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '22010202',    'nombre' => 'PARTICULAR 2',                                    'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '220103',      'nombre' => 'OTROS PRESTAMOS A LARGO PLAZO',                   'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '22010301',    'nombre' => 'PRESTAMO 1',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '22010302',    'nombre' => 'PRESTAMO 2',                                      'tipo' => 'PASIVO',        'naturaleza' => 'ACREEDORA', 'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            // ===================== PATRIMONIO NETO =====================
            ['codigo' => '3',           'nombre' => 'PATRIMONIO NETO',                                 'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '31',          'nombre' => 'CAPITAL CONTABLE',                                'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '3101',        'nombre' => 'CAPITAL LIQUIDO',                                 'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '310101',      'nombre' => 'CAPITAL LIQUIDO HUGO MUÑOZ',                      'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '3102',        'nombre' => 'UTILIDADES RETENIDAS',                            'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '310201',      'nombre' => 'UTILIDADES DEL EJERCICIO ANTERIOR',               'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '310202',      'nombre' => 'UTILIDADES DEL EJERCICIO ACTUAL',                 'tipo' => 'PATRIMONIO',    'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '3103',        'nombre' => 'PERDIDAS POR APLICAR (CR)',                       'tipo' => 'PATRIMONIO',    'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '310301',      'nombre' => 'PERDIDAS DEL EJERCICIO ANTERIOR',                 'tipo' => 'PATRIMONIO',    'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '310302',      'nombre' => 'PERDIDAS DEL EJERCICIO ACTUAL',                   'tipo' => 'PATRIMONIO',    'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            // ===================== COSTOS Y GASTOS =====================
            ['codigo' => '4',           'nombre' => 'COSTOS Y GASTOS',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '41',          'nombre' => 'COSTOS Y GASTOS OPERATIVOS',                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '4101',        'nombre' => 'COSTO DE VENTAS',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '410101',      'nombre' => 'COSTO DE VENTAS',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '41010101',    'nombre' => 'COSTO DE VENTAS DE PRODUCTOS',                    'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '4102',        'nombre' => 'GASTOS OPERATIVOS',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '410201',      'nombre' => 'GASTOS DE VENTAS',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '41020101',    'nombre' => 'SUELDOS Y SALARIOS',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020102',    'nombre' => 'HORAS EXTRAS',                                    'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020103',    'nombre' => 'COMISIONES SOBRE VENTAS',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020104',    'nombre' => 'HONORARIOS',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020105',    'nombre' => 'BONIFICACIONES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020106',    'nombre' => 'GRATIFICACIONES',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020107',    'nombre' => 'AGUINALDOS',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020108',    'nombre' => 'INDEMNIZACIONES',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020109',    'nombre' => 'VACACIONES',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020110',    'nombre' => 'PREMIOS POR META',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020111',    'nombre' => 'CUOTA PATRONAL ISSS',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020112',    'nombre' => 'CUOTA PATRONAL AFP',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020113',    'nombre' => 'CUOTA PATRONAL INSAFORP',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020114',    'nombre' => 'ATENCIONES AL PERSONAL',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020115',    'nombre' => 'CAPACITACIONES AL PERSONAL',                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020116',    'nombre' => 'SERVICIO DE AGUA',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020117',    'nombre' => 'ENERGIA ELECTRICA',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020118',    'nombre' => 'SERVICIO DE TELEFONO',                            'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020119',    'nombre' => 'ALQUILER DE LOCAL',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020120',    'nombre' => 'COMBUSTIBLE Y LUBRICANTES',                       'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020121',    'nombre' => 'MANTENIMIENTO Y REPARACIONES',                    'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020122',    'nombre' => 'REPUESTOS Y HERRAMIENTAS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020123',    'nombre' => 'DEPRECIACIONES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020124',    'nombre' => 'VIATICOS Y HOSPEDAJES',                           'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020125',    'nombre' => 'TRANSPORTE Y FLETES',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020126',    'nombre' => 'SANEAMIENTO Y LIMPIEZA',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020127',    'nombre' => 'PROTECCION Y VIGILANCIA',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020128',    'nombre' => 'ESTACIONAMIENTO, PEAJES Y FOVIAL',                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020129',    'nombre' => 'CORREOS Y ENCONMIENDAS',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020130',    'nombre' => 'ARRENDAMIENTO DE EQUIPOS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020131',    'nombre' => 'SEGUROS Y FIANZAS',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020132',    'nombre' => 'DECORACIONES, ARREGLOS Y MEJORAS',                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020133',    'nombre' => 'PAPELERIA Y UTILES',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020134',    'nombre' => 'PUBLICIDAD Y PROPAGANDA',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020135',    'nombre' => 'SUSCRIPCIONES Y ANUNCIOS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020136',    'nombre' => 'GASTOS DE REPRESENTACION',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020137',    'nombre' => 'IMPUESTOS MUNICIPALES',                           'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020138',    'nombre' => 'CUENTAS INCOBRABLES',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020139',    'nombre' => 'DERECHOS REGISTRALES Y MATRICULAS',               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020140',    'nombre' => 'GASTOS LEGALES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020141',    'nombre' => 'CONTRIBUCIONES Y DONACIONES',                     'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020142',    'nombre' => 'GASTOS NO DEDUCIBLES',                            'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020199',    'nombre' => 'VARIOS',                                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '410202',      'nombre' => 'GASTOS DE ADMINISTRACION',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '41020201',    'nombre' => 'SUELDOS Y SALARIOS',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020202',    'nombre' => 'HORAS EXTRAS',                                    'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020203',    'nombre' => 'COMISIONES SOBRE COBROS',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020204',    'nombre' => 'HONORARIOS',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020205',    'nombre' => 'BONIFICACIONES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020206',    'nombre' => 'GRATIFICACIONES',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020207',    'nombre' => 'AGUINALDOS',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020208',    'nombre' => 'INDEMNIZACIONES',                                 'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020209',    'nombre' => 'VACACIONES',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020210',    'nombre' => 'PREMIOS POR META',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020211',    'nombre' => 'CUOTA PATRONAL ISSS',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020212',    'nombre' => 'CUOTA PATRONAL AFP',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020213',    'nombre' => 'CUOTA PATRONAL INSAFORP',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020214',    'nombre' => 'ATENCIONES AL PERSONAL',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020215',    'nombre' => 'CAPACITACIONES AL PERSONAL',                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020216',    'nombre' => 'SERVICIO DE AGUA',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020217',    'nombre' => 'ENERGIA ELECTRICA',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020218',    'nombre' => 'SERVICIO DE TELEFONO',                            'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020219',    'nombre' => 'ALQUILER DE LOCAL',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020220',    'nombre' => 'COMBUSTIBLE Y LUBRICANTES',                       'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020221',    'nombre' => 'MANTENIMIENTO Y REPARACIONES',                    'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020222',    'nombre' => 'REPUESTOS Y HERRAMIENTAS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020223',    'nombre' => 'DEPRECIACIONES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020224',    'nombre' => 'VIATICOS Y HOSPEDAJES',                           'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020225',    'nombre' => 'TRANSPORTE Y FLETES',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020226',    'nombre' => 'SANEAMIENTO Y LIMPIEZA',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020227',    'nombre' => 'PROTECCION Y VIGILANCIA',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020228',    'nombre' => 'ESTACIONAMIENTO, PEAJES Y FOVIAL',                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020229',    'nombre' => 'CORREOS Y ENCONMIENDAS',                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020230',    'nombre' => 'ARRENDAMIENTO DE EQUIPOS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020231',    'nombre' => 'SEGUROS Y FIANZAS',                               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020232',    'nombre' => 'DECORACIONES, ARREGLOS Y MEJORAS',                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020233',    'nombre' => 'PAPELERIA Y UTILES',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020234',    'nombre' => 'PUBLICIDAD Y PROPAGANDA',                         'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020235',    'nombre' => 'SUSCRIPCIONES Y ANUNCIOS',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020236',    'nombre' => 'GASTOS DE REPRESENTACION',                        'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020237',    'nombre' => 'IMPUESTOS MUNICIPALES',                           'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020238',    'nombre' => 'CUENTAS INCOBRABLES',                             'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020239',    'nombre' => 'DERECHOS REGISTRALES Y MATRICULAS',               'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020240',    'nombre' => 'GASTOS LEGALES',                                  'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020241',    'nombre' => 'CONTRIBUCIONES Y DONACIONES',                     'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020242',    'nombre' => 'GASTOS NO DEDUCIBLES',                            'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020299',    'nombre' => 'VARIOS',                                          'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '410203',      'nombre' => 'GASTOS FINANCIEROS',                              'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '41020301',    'nombre' => 'INTERESES',                                       'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020302',    'nombre' => 'COMISIONES',                                      'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '41020303',    'nombre' => 'GASTOS BANCARIOS',                                'tipo' => 'COSTO Y GASTO', 'naturaleza' => 'DEUDORA',   'nivel' => 5, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            // ===================== INGRESOS =====================
            ['codigo' => '5',           'nombre' => 'INGRESOS',                                        'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '51',          'nombre' => 'INGRESOS OPERATIVOS',                             'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],

            ['codigo' => '5101',        'nombre' => 'VENTAS LOCALES',                                  'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '510101',      'nombre' => 'VENTAS INTERNAS',                                 'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '510102',      'nombre' => 'DEVOLUCION SOBRE VENTA (CR)',                     'tipo' => 'INGRESO',       'naturaleza' => 'DEUDORA',   'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '510103',      'nombre' => 'SERVICIOS DE ALQUILER',                           'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '510104',      'nombre' => 'INTERESES',                                       'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],
            ['codigo' => '510105',      'nombre' => 'OTROS',                                           'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            ['codigo' => '5102',        'nombre' => 'OTROS INGRESOS',                                  'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '510201',      'nombre' => 'INGRESOS POR INTERESES',                          'tipo' => 'INGRESO',       'naturaleza' => 'ACREEDORA', 'nivel' => 4, 'cuenta_padre_id' => null, 'acepta_movimientos' => 1],

            // ===================== CUENTAS DE CIERRE =====================
            ['codigo' => '6',           'nombre' => 'CUENTAS DE CIERRE',                               'tipo' => 'TRANSITORIAS',  'naturaleza' => 'DEUDORA',   'nivel' => 1, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '61',          'nombre' => 'CUENTAS DE CIERRE',                               'tipo' => 'TRANSITORIAS',  'naturaleza' => 'DEUDORA',   'nivel' => 2, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
            ['codigo' => '6101',        'nombre' => 'DEFICIT Y EXCEDENTES',                            'tipo' => 'TRANSITORIAS',  'naturaleza' => 'DEUDORA',   'nivel' => 3, 'cuenta_padre_id' => null, 'acepta_movimientos' => 0],
        ];

        // Insert all and build code=>id map
        $db = \Config\Database::connect();
        $codeToId = [];

        foreach ($cuentas as $c) {
            $c['activo']     = 1;
            $c['created_at'] = $now;
            $c['updated_at'] = $now;
            $c['cuenta_padre_id'] = null; // will be resolved below

            $db->table('cont_plan_cuentas')->insert($c);
            $codeToId[$c['codigo']] = $db->insertID();
        }

        // Update parent IDs by resolving codes
        foreach ($cuentas as $c) {
            $id    = $codeToId[$c['codigo']] ?? null;
            $parId = null;
            $parts = explode('.', $c['codigo']);

            if (count($parts) > 1) {
                array_pop($parts);
                $parentCode = implode('.', $parts);
                $parId = $codeToId[$parentCode] ?? null;
            }

            if ($id && $parId !== null) {
                $db->table('cont_plan_cuentas')->where('id', $id)->update(['cuenta_padre_id' => $parId]);
            }
        }
    }
}
