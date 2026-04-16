<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContPlanCuentasSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $cuentas = [
            // ===================== ACTIVOS =====================
            ['codigo'=>'1',       'nombre'=>'ACTIVOS',                             'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1',     'nombre'=>'ACTIVOS CORRIENTES',                  'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.1',   'nombre'=>'EFECTIVO Y EQUIVALENTES',             'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.1.01','nombre'=>'Caja General',                        'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.1.02','nombre'=>'Caja Chica',                          'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.1.03','nombre'=>'Bancos',                              'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.2',   'nombre'=>'CUENTAS POR COBRAR',                  'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.2.01','nombre'=>'Clientes',                            'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.2.02','nombre'=>'Documentos por Cobrar',               'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.2.03','nombre'=>'Otras Cuentas por Cobrar',            'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.3',   'nombre'=>'INVENTARIOS',                         'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.3.01','nombre'=>'Inventario de Mercadería',            'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.3.02','nombre'=>'Inventario de Materiales',            'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.4',   'nombre'=>'GASTOS PAGADOS POR ANTICIPADO',       'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.4.01','nombre'=>'Seguros Prepagados',                  'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.4.02','nombre'=>'Alquileres Prepagados',               'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.1.5',   'nombre'=>'IMPUESTOS POR RECUPERAR',             'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.1.5.01','nombre'=>'IVA Crédito Fiscal',                  'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.2',     'nombre'=>'ACTIVOS NO CORRIENTES',               'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.2.1',   'nombre'=>'PROPIEDAD, PLANTA Y EQUIPO',          'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'1.2.1.01','nombre'=>'Mobiliario y Equipo de Oficina',      'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.2.1.02','nombre'=>'Equipo de Cómputo',                   'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.2.1.03','nombre'=>'Vehículos',                           'tipo'=>'ACTIVO',  'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'1.2.1.04','nombre'=>'Depreciación Acumulada',              'tipo'=>'ACTIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],

            // ===================== PASIVOS =====================
            ['codigo'=>'2',       'nombre'=>'PASIVOS',                             'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.1',     'nombre'=>'PASIVOS CORRIENTES',                  'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.1.1',   'nombre'=>'CUENTAS POR PAGAR',                   'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.1.1.01','nombre'=>'Proveedores',                         'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.1.02','nombre'=>'Documentos por Pagar',                'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.2',   'nombre'=>'ACUMULACIONES Y RETENCIONES',         'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.1.2.01','nombre'=>'Salarios por Pagar',                  'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.2.02','nombre'=>'ISSS por Pagar',                      'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.2.03','nombre'=>'AFP por Pagar',                       'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.2.04','nombre'=>'Renta por Pagar',                     'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.2.05','nombre'=>'IVA Débito Fiscal',                   'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.1.3',   'nombre'=>'ANTICIPOS DE CLIENTES',               'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.1.3.01','nombre'=>'Anticipos de Clientes',               'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'2.2',     'nombre'=>'PASIVOS NO CORRIENTES',               'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.2.1',   'nombre'=>'PRÉSTAMOS BANCARIOS LARGO PLAZO',     'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'2.2.1.01','nombre'=>'Préstamos Bancarios L/P',             'tipo'=>'PASIVO',  'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],

            // ===================== CAPITAL =====================
            ['codigo'=>'3',       'nombre'=>'CAPITAL',                             'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.1',     'nombre'=>'CAPITAL SOCIAL',                      'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.1.1',   'nombre'=>'CAPITAL SOCIAL',                      'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.1.1.01','nombre'=>'Capital Social Pagado',               'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'3.2',     'nombre'=>'RESERVAS',                            'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.2.1',   'nombre'=>'RESERVA LEGAL',                       'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.2.1.01','nombre'=>'Reserva Legal',                       'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'3.3',     'nombre'=>'RESULTADOS',                          'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.3.1',   'nombre'=>'UTILIDADES',                          'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'3.3.1.01','nombre'=>'Utilidades de Ejercicios Anteriores', 'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'3.3.1.02','nombre'=>'Utilidad del Ejercicio Actual',       'tipo'=>'CAPITAL', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'3.3.1.03','nombre'=>'Pérdida del Ejercicio',               'tipo'=>'CAPITAL', 'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],

            // ===================== INGRESOS =====================
            ['codigo'=>'4',       'nombre'=>'INGRESOS',                            'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'4.1',     'nombre'=>'INGRESOS DE OPERACIÓN',               'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'4.1.1',   'nombre'=>'VENTAS',                              'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'4.1.1.01','nombre'=>'Ventas de Productos',                 'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'4.1.1.02','nombre'=>'Ventas de Servicios',                 'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'4.1.1.03','nombre'=>'Devoluciones sobre Ventas',           'tipo'=>'INGRESO', 'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'4.1.2',   'nombre'=>'OTROS INGRESOS',                      'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'4.1.2.01','nombre'=>'Ingresos Financieros',                'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'4.1.2.02','nombre'=>'Otros Ingresos',                      'tipo'=>'INGRESO', 'naturaleza'=>'ACREEDORA', 'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],

            // ===================== COSTOS =====================
            ['codigo'=>'5',       'nombre'=>'COSTOS',                              'tipo'=>'COSTO',   'naturaleza'=>'DEUDORA',   'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'5.1',     'nombre'=>'COSTO DE VENTAS',                     'tipo'=>'COSTO',   'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'5.1.1',   'nombre'=>'COSTO DE VENTAS',                     'tipo'=>'COSTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'5.1.1.01','nombre'=>'Costo de Productos Vendidos',         'tipo'=>'COSTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'5.1.1.02','nombre'=>'Costo de Servicios Prestados',        'tipo'=>'COSTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],

            // ===================== GASTOS =====================
            ['codigo'=>'6',       'nombre'=>'GASTOS',                              'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>1, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.1',     'nombre'=>'GASTOS DE ADMINISTRACIÓN',            'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.1.1',   'nombre'=>'GASTOS DE PERSONAL',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.1.1.01','nombre'=>'Salarios y Sueldos',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.1.02','nombre'=>'Cuota Patronal ISSS',                 'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.1.03','nombre'=>'Cuota Patronal AFP',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.1.04','nombre'=>'Vacaciones',                          'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.1.05','nombre'=>'Aguinaldos',                          'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2',   'nombre'=>'GASTOS GENERALES',                    'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.1.2.01','nombre'=>'Alquileres',                          'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2.02','nombre'=>'Servicios Básicos',                   'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2.03','nombre'=>'Papelería y Útiles',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2.04','nombre'=>'Depreciaciones',                      'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2.05','nombre'=>'Comunicaciones',                      'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.1.2.06','nombre'=>'Mantenimiento y Reparaciones',        'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.2',     'nombre'=>'GASTOS DE VENTA',                     'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.2.1',   'nombre'=>'GASTOS DE MARKETING',                 'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.2.1.01','nombre'=>'Publicidad y Mercadeo',               'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.2.1.02','nombre'=>'Comisiones de Ventas',                'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.2.2',   'nombre'=>'GASTOS DE DISTRIBUCIÓN',              'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.2.2.01','nombre'=>'Transporte y Flete',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.3',     'nombre'=>'GASTOS FINANCIEROS',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>2, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.3.1',   'nombre'=>'GASTOS FINANCIEROS',                  'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>3, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>0],
            ['codigo'=>'6.3.1.01','nombre'=>'Intereses Pagados',                   'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
            ['codigo'=>'6.3.1.02','nombre'=>'Comisiones Bancarias',                'tipo'=>'GASTO',   'naturaleza'=>'DEUDORA',   'nivel'=>4, 'cuenta_padre_id'=>null, 'acepta_movimientos'=>1],
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
