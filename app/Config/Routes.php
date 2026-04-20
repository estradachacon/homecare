<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');
$routes->get('api/backup/estrada', 'Api\BackupController::index');

// Recuperación de contraseña (SIN AUTH)
$routes->group('auth', function ($routes) {
    $routes->post('send-reset-code', 'AuthController::sendResetCode');
    $routes->post('verify-reset-code', 'AuthController::verifyResetCode');
    $routes->post('reset-password', 'AuthController::resetPassword');
});

$routes->group('', ['filter' => 'auth'], function ($routes) {    // Grupo del Dashboard (requiere autenticación)
    $routes->get('/dashboard', 'DashboardController::index'); // Página principal del dashboard

    // Módulo de Pedidos
    $routes->group('orders', function ($routes) {
        $routes->resource('orders', [
            'controller' => 'OrderController'
        ]);
    });

    // Módulo de Reportes
    $routes->group('reports', function ($routes) {
        $routes->get('packages', 'ReportController::packages');
        $routes->post('packages', 'ReportController::packages');
        $routes->get('packages/excel', 'ReportController::packagesExcel');
        $routes->get('packages/pdf', 'ReportController::packagesPDF');
        $routes->get('trans', 'ReportController::trans');
        $routes->post('trans', 'ReportController::trans');
        $routes->get('trans/excel', 'ReportController::transExcel');
        $routes->get('trans/pdf', 'ReportController::transPDF');
        $routes->get('cashiersmovements', 'ReportController::cashiersmovements');
        $routes->post('cashiersmovements', 'ReportController::cashiersmovements');
        $routes->get('cashiersmovements/excel', 'ReportController::cashiersmovementsExcel');
        $routes->get('cashiersmovements/pdf', 'ReportController::cashiersmovementsPDF');
        $routes->get('users', 'ReportController::users');
        $routes->post('generate', 'ReportController::generate');
    });

    //Rutas para las notificaciones
    $routes->get('notifications/ultimas', 'Notifications::ultimas');
    $routes->get('notifications/leer/(:num)', 'Notifications::marcarLeida/$1');
    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications-search', 'Notifications::search');

    // Mantenimientos de cajas
    $routes->presenter('cashiers', ['controller' => 'CashierController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('cashiers/delete', 'CashierController::delete');
    $routes->get('cashier/session/status', 'CashierController::sessionStatus');
    $routes->post('cashier/open', 'CashierController::open');
    $routes->get('cashier/available-amount', 'RemunerationController::availableAmount');
    $routes->get('cashier/transactions', 'CashierController::transactions');
    $routes->get('cashiers/summary/(:num)', 'CashierController::summary/$1');
    $routes->post('cashiers/close', 'CashierController::close');

    // Módulo de mantenimiento de usuarios
    $routes->presenter('users', ['controller' => 'UserController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('users/delete', 'UserController::delete');

    // Módulo de mantenimiento de sucursales
    $routes->get('branches-list', 'BranchController::list');
    $routes->presenter('branches', ['controller' => 'BranchController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);

    // Mantenimiento de sistema
    $routes->presenter('settings', ['controller' => 'SettingsController', 'only' => ['index', 'update']]);
    $routes->get('settings/edit', 'SettingsController::edit');
    $routes->post('settings/update', 'SettingsController::update');
    $routes->get('tools/clear-browser', 'SystemTools::clearClientData', ['filter' => 'auth']);
    $routes->get('system/logout-all', 'SystemTools::logoutAll', ['filter' => 'auth']);
    $routes->get('logs', 'BitacoraController::index');

    // Rutas para perfiles
    $routes->get('perfil', 'ProfileController::index');
    $routes->post('perfil/update', 'ProfileController::update');

    // Select2 Colonias
    $routes->get('ajax/colonias/search', 'UbicacionesController::searchColonias');

    // Módulo de mantenimiento de cuentas
    $routes->presenter('accounts', ['controller' => 'AccountController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('accounts/delete', 'AccountController::delete');
    $routes->get('accounts/searchAjax', 'AccountController::searchAjax');
    $routes->get('accounts-list', 'AccountController::list');
    $routes->post('accounts-transfer', 'AccountController::processTransfer');

    // Rutas para el módulo de transacciones
    $routes->get('transactions', 'TransactionsController::index');
    $routes->post('transactions/addSalida', 'TransactionsController::addSalida');

    //Rutas para el mantenimiento de roles 
    $routes->presenter('roles', ['controller' => 'RoleController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('roles/delete', 'RoleController::delete');
    $routes->get('access/(:num)', 'RoleController::access/$1');
    $routes->put('access/(:num)', 'RoleController::saveAccess/$1');

    //Rutas para reportería
    $routes->get('reports', 'ReportesController::index');
    // -- Reportes Maestro
    $routes->get('reports/saldos-antiguedad', 'ReportesController::formSaldosAntiguedad');
    $routes->get('reports/saldos-antiguedad-pdf', 'ReportesController::saldosAntiguedadPDF');
    $routes->get('reports/saldos-antiguedad', 'ReportesController::saldosAntiguedad');
    $routes->get('reports/saldos-antiguedad-detalle-pdf', 'ReportesController::saldosAntiguedadDetallePDF');
    // -- Reportes Vendedor
    $routes->get('reports/saldos-antiguedad-vendedor-pdf', 'ReportesController::saldosAntiguedadVendedorPDF');
    $routes->get('reports/saldos-antiguedad-vendedor-detalle-pdf', 'ReportesController::saldosAntiguedadVendedorDetallePDF');
    // -- Reportes de Facturación
    $routes->get('reports/facturacion', 'ReportesController::facturacion');
    $routes->get('reports/facturacion-pdf', 'ReportesController::facturacionPDF');
    $routes->get('reports/facturacion-excel', 'ReportesController::facturacionExcel');
    $routes->get('reports/ventas-cliente-pdf', 'ReportesController::ventasClientePDF');
    $routes->get('reports/ventas-cliente-excel', 'ReportesController::ventasClienteExcel');
    $routes->get('reports/ventas-tipo-pdf', 'ReportesController::ventasTipoPDF');
    $routes->get('reports/ventas-tipo-excel', 'ReportesController::ventasTipoExcel');
    // -- Reportes Facturación/Vendedor (pruebas)
    $routes->get('reports/facturacion-vendedores-pdf', 'ReportesController::facturacionVendedoresPDF');
    $routes->get('reports/ventas-vendedores-excel', 'ReportesController::ventasVendedoresExcel');
    // -- Reporte de estados de cuenta
    $routes->get('reports/estado-cuenta-cliente-pdf', 'ReportesController::estadoCuentaClientePdf');
    // -- Reportes de quedans
    $routes->get('reports/quedans', 'ReportesController::quedans');
    $routes->get('reports/quedans-pdf', 'ReportesController::quedansPdf');
    $routes->get('reports/quedans-detalle-pdf', 'ReportesController::quedansDetallePdf');
    $routes->get('reports/quedans-excel', 'ReportesController::quedansExcel');

    // Rutas para el módulo de facturación
    $routes->get('facturas', 'Facturas::index');
    $routes->get('facturas/carga', 'Facturas::carga');
    $routes->post('facturas/cargar', 'Facturas::procesarCarga');
    $routes->post('facturas/validar-numero-control', 'Facturas::validarNumeroControl');
    $routes->get('facturas/(:num)', 'Facturas::detalle/$1');
    $routes->post('facturas/anular/(:num)', 'Facturas::anular/$1');
    $routes->post('facturas/validar-documento-relacionado', 'Facturas::validarDocumentoRelacionado');

    //Modulo de vendedores
    $routes->presenter('sellers', ['controller' => 'SellerController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->get('sellers-search', 'SellerController::search');
    $routes->post('sellers/delete', 'SellerController::delete');
    $routes->post('sellers/create-ajax', 'SellerController::createAjax');
    $routes->get('sellers/searchAjax', 'SellerController::searchAjax');
    $routes->get('sellers/filter-for-packages', 'SellerController::filterForPackages');
    $routes->post('facturas/cambiar-vendedor', 'Facturas::cambiarVendedor');

    // Rutas para el módulo de clientes
    $routes->get('clientes', 'ClienteController::index');
    $routes->get('clientes/(:num)', 'ClienteController::show/$1');
    $routes->get('clientes/buscar', 'ClienteController::buscar');
    $routes->get('clientes/buscarparaDTE', 'ClienteController::buscarparaDTE');

    // Rutas para mantenimiento de tipos de venta
    $routes->get('tipo_venta', 'TipoVentaController::index');
    $routes->get('tipo_venta/searchAjax', 'TipoVentaController::searchAjax');
    $routes->get('tipo_venta-search', 'TipoVentaController::search');
    $routes->post('tipo_venta/delete', 'TipoVentaController::delete');
    $routes->presenter('tipo_venta', ['controller' => 'TipoVentaController', 'only' => ['new', 'create', 'edit', 'update']]);

    // Rutas para el módulo de pagos
    $routes->presenter('payments', ['controller' => 'PaymentController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->get('payments/facturas/(:num)', 'PaymentController::facturas/$1');
    $routes->get('payments/facturasPendientes/(:num)', 'PaymentController::facturasPendientes/$1');
    $routes->get('accounts-search', 'AccountController::search');
    $routes->get('facturas/preview/(:num)', 'Facturas::preview/$1');
    $routes->get('facturas/checkPagos/(:num)', 'Facturas::checkPagos/$1');
    $routes->post('payments/store', 'PaymentController::store');
    $routes->get('payments/(:num)', 'PaymentController::show/$1');
    $routes->get('payments/anular/(:num)', 'PaymentController::anular/$1');

    // Rutas para el módulo de inventarios
    $routes->get('inventory', 'InventoryController::index');
    $routes->post('productos/delete/(:num)', 'InventoryController::delete/$1');
    $routes->get('productos/searchAjax', 'InventoryController::searchAjax');
    $routes->get('productos/(:num)', 'InventoryController::show/$1');
    $routes->get('inventory/excel', 'InventoryController::excel');
    $routes->post('inventory/update/(:num)', 'InventoryController::update/$1');

    // Rutas para mantenimiento de proveedores
    $routes->get('proveedores', 'ProveedorController::index');
    $routes->get('proveedores/new', 'ProveedorController::new');
    $routes->post('proveedores/create', 'ProveedorController::create');
    $routes->get('proveedores/edit/(:num)', 'ProveedorController::edit/$1');
    $routes->post('proveedores/update/(:num)', 'ProveedorController::update/$1');
    $routes->post('proveedores/delete', 'ProveedorController::delete');
    $routes->get('proveedores-search', 'ProveedorController::search');
    $routes->post('proveedores/createAjax', 'ProveedorController::createAjax');
    $routes->get('proveedores/searchAjax', 'ProveedorController::searchAjax');

    // Rutas para Mantenimiento de Compras
    $routes->get('purchases', 'ComprasController::index');
    $routes->get('purchases/new', 'ComprasController::new');
    $routes->get('purchases/load', 'ComprasController::carga');
    $routes->post('purchases/processload', 'ComprasController::procesarCarga');
    $routes->post('purchases/validar-productos', 'ComprasController::validarProductos');
    $routes->get('purchases/(:num)', 'ComprasController::show/$1');
    $routes->post('purchases/delete/(:num)', 'ComprasController::delete/$1');
    $routes->post('purchases/validar-documento', 'ComprasController::validarDocumento');
    $routes->get('compras/preview/(:num)', 'ComprasController::preview/$1');

    // Rutas para mantenimiento de los pagos a compras
    $routes->get('compraspagos', 'ComprasPagosController::index');
    $routes->get('compraspagos/new',    'ComprasPagosController::new');
    $routes->post('compraspagos/store', 'ComprasPagosController::store');
    $routes->get('compraspagos/(:num)', 'ComprasPagosController::show/$1');
    $routes->get('compraspagos/anular/(:num)',            'ComprasPagosController::anular/$1');
    $routes->get('compraspagos/comprasPendientes/(:num)', 'ComprasPagosController::comprasPendientes/$1');


    // ─── MÓDULO DE CONSIGNACIONES ─────────────────────────────────────────────
    $routes->get('consignaciones',                               'ConsignacionesController::index');
    $routes->get('consignaciones/crear',                         'ConsignacionesController::crear');
    $routes->post('consignaciones/guardar',                      'ConsignacionesController::guardar');
    $routes->get('consignaciones/(:num)',                        'ConsignacionesController::show/$1');
    $routes->get('consignaciones/(:num)/imprimir',               'ConsignacionesController::imprimir/$1');
    $routes->get('consignaciones/(:num)/cerrar',                 'ConsignacionesController::cerrar/$1');
    $routes->post('consignaciones/(:num)/procesar-cierre',       'ConsignacionesController::procesarCierre/$1');
    $routes->post('consignaciones/(:num)/anular',                'ConsignacionesController::anular/$1');
    $routes->get('consignaciones/precio-ajax',                   'ConsignacionesController::getPrecioAjax');
    $routes->get('consignaciones/facturas-vendedor/(:num)',       'ConsignacionesController::facturasVendedor/$1');
    // Precios
    $routes->get('consignaciones/precios',                       'ConsignacionesController::precios');
    $routes->post('consignaciones/precios/guardar',              'ConsignacionesController::guardarPrecio');
    $routes->post('consignaciones/precios/(:num)/eliminar',      'ConsignacionesController::eliminarPrecio/$1');

    // Rutas para mantenimiento de Queda
    $routes->get('quedans', 'Quedans::index');
    $routes->get('quedans/crear', 'Quedans::crear');
    $routes->post('quedans/guardar', 'Quedans::guardar');
    $routes->get('quedans/facturas-cliente/(:num)', 'Quedans::facturasCliente/$1');
    $routes->get('quedans/(:num)', 'Quedans::show/$1');
    $routes->post('quedans/anular/(:num)', 'Quedans::anular/$1');

    // Rutas para mantenimiento de Comisiones
    $routes->get('comisiones/', 'Comisiones::index');
    $routes->get('comisiones/(:num)', 'Comisiones::ver/$1');
    $routes->get('comisiones/configuracion', 'Comisiones::config');
    $routes->post('comisiones/guardarGeneral', 'Comisiones::guardarGeneral');
    $routes->post('comisiones/guardarVendedores', 'Comisiones::guardarVendedores');
    $routes->post('comisiones/guardarReglas', 'Comisiones::guardarReglas');
    $routes->post('comisiones/guardarMargen', 'Comisiones::guardarMargen');
    $routes->post('comisiones/vendedor/add', 'Comisiones::addVendedor');
    $routes->post('comisiones/vendedor/update', 'Comisiones::updateVendedor');
    $routes->post('comisiones/vendedor/delete', 'Comisiones::deleteVendedor');
    $routes->get('comisiones/generar', 'Comisiones::generar');
    $routes->post('comisiones/getDocumentos', 'Comisiones::getDocumentos');
    $routes->post('comisiones/guardar', 'Comisiones::guardar');
    
    //Rutas para pruebas locales de autenticacion y emisión
    $routes->get('test-hacienda', 'TestController::hacienda');

    //Emision de DTE legal
    $routes->get('factura/crear', 'DteController::new');

    // ─── MÓDULO DE CONTABILIDAD ────────────────────────────────────
    $routes->get('contabilidad', 'ContabilidadController::index');

    // Plan de Cuentas
    $routes->get('contabilidad/plan-cuentas',            'ContPlanCuentasController::index');
    $routes->post('contabilidad/plan-cuentas/store',     'ContPlanCuentasController::store');
    $routes->post('contabilidad/plan-cuentas/update/(:num)', 'ContPlanCuentasController::update/$1');
    $routes->post('contabilidad/plan-cuentas/delete',    'ContPlanCuentasController::delete');
    $routes->get('contabilidad/plan-cuentas/search',     'ContPlanCuentasController::searchAjax');
    $routes->get('contabilidad/plan-cuentas/get/(:num)', 'ContPlanCuentasController::getById/$1');

    // Períodos
    $routes->get('contabilidad/periodos',                    'ContPeriodosController::index');
    $routes->post('contabilidad/periodos/store',             'ContPeriodosController::store');
    $routes->post('contabilidad/periodos/cerrar/(:num)',      'ContPeriodosController::cerrar/$1');
    $routes->post('contabilidad/periodos/reabrir/(:num)',     'ContPeriodosController::reabrir/$1');

    // Asientos Contables
    $routes->get('contabilidad/asientos',                    'ContAsientosController::index');
    $routes->get('contabilidad/asientos/nuevo',              'ContAsientosController::nuevo');
    $routes->post('contabilidad/asientos/store',             'ContAsientosController::store');
    $routes->get('contabilidad/asientos/(:num)',             'ContAsientosController::show/$1');
    $routes->post('contabilidad/asientos/aprobar/(:num)',    'ContAsientosController::aprobar/$1');
    $routes->post('contabilidad/asientos/anular/(:num)',     'ContAsientosController::anular/$1');

    // Listados
    $routes->get('contabilidad/listados/relacion-cuentas',   'ContReportesController::relacionCuentas');
    $routes->get('contabilidad/listados/costos',             'ContReportesController::costos');
    $routes->get('contabilidad/listados/gastos',             'ContReportesController::gastos');
    $routes->get('contabilidad/listados/comparativos',       'ContReportesController::comparativos');
    $routes->get('contabilidad/listados/catalogos',          'ContReportesController::catalogos');

    // Reportes
    $routes->get('contabilidad/reportes/diario',             'ContReportesController::diario');
    $routes->get('contabilidad/reportes/mayor',              'ContReportesController::mayor');
    $routes->get('contabilidad/reportes/auxiliar',           'ContReportesController::auxiliar');

    // Procesos
    $routes->get('contabilidad/procesos/cierre-mes',                 'ContProcesosController::cierreMes');
    $routes->post('contabilidad/procesos/cierre-mes/ejecutar',       'ContProcesosController::ejecutarCierreMes');
    $routes->get('contabilidad/procesos/cierre-anual',               'ContProcesosController::cierreAnual');
    $routes->post('contabilidad/procesos/cierre-anual/ejecutar',     'ContProcesosController::ejecutarCierreAnual');

    // Mantenimientos
    $routes->get('contabilidad/mantenimientos/acumulados',           'ContReportesController::acumuladosActuales');
    $routes->get('contabilidad/mantenimientos/acumulados-historicos','ContReportesController::acumuladosHistoricos');
    $routes->get('contabilidad/mantenimientos/transacciones-hist',   'ContReportesController::transaccionesHistoricas');

    // Configuración
    $routes->get('contabilidad/configuracion',               'ContConfiguracionController::index');
    $routes->post('contabilidad/configuracion/guardar',      'ContConfiguracionController::guardar');
});
