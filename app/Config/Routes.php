<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

// 🔐 Recuperación de contraseña (SIN AUTH)
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
        // Rutas adicionales
        $routes->get('(:num)/invoice', 'OrderController::invoice/$1');
        $routes->post('(:num)/cancel', 'OrderController::cancel/$1');
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

    // Remuneraciones de paquetes
    $routes->get('remu/create', 'RemunerationController::create');
    $routes->post('payments/pay-seller', 'PaymentController::paySeller');
    $routes->get('payments/packages-by-seller/(:num)', 'PaymentController::packagesBySeller/$1');

    // Remuneraciones de paquetes por cuenta
    $routes->get('remuaccount/create', 'RemunerationController::byAccountCreate');
    $routes->post('payments/pay-seller-byaccount', 'PaymentController::paySellerbyAccount');
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

    // Módulo de mantenimiento de vendedores
    $routes->presenter('sellers', ['controller' => 'SellerController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->get('sellers-search', 'SellerController::search');
    $routes->post('sellers/delete', 'SellerController::delete');
    $routes->post('sellers/create-ajax', 'SellerController::createAjax');
    $routes->get('sellers/searchAjax', 'SellerController::searchAjax');
    $routes->get('sellers/filter-for-packages', 'SellerController::filterForPackages');

    // Módulo de mantenimiento de puntos fijos
    $routes->presenter('settledpoint', ['controller' => 'SettledPointController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('settledpoint/delete', 'SettledPointController::delete');
    $routes->get('settledPoints/getList', 'SettledPointController::getList');
    $routes->get('settledPoints/getDays/(:num)', 'SettledPointController::getAvailableDays/$1');

    // Módulo de mantenimiento de rutas
    $routes->presenter('routes', ['controller' => 'RouteController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('routes/delete', 'RouteController::delete');

    // Módulo de mantenimiento de paquetes
    $route['upload-paquete'] = 'PackageController/subirImagen';
    $routes->post('packages/store', 'PackageController::store');
    $routes->post('packages-setDestino', 'PackageController::setDestino');
    $routes->post('packages-setReenvio', 'PackageController::setReenvio');
    $routes->post('packages-devolver/(:num)', 'PackageController::devolver/$1');
    $routes->post('packages-entregar/(:num)', 'PackageController::entregar/$1');
    $routes->get('packages-getDestinoInfo/(:num)', 'PackageController::getDestinoInfo/$1');
    $routes->get('packages/return', 'PackageController::showReturnPackages');
    $routes->presenter('packages', ['controller' => 'PackageController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete', 'show']]);

    // Módulo de mantenimiento de tracking
    $routes->presenter('tracking', ['controller' => 'TrackingController', 'only' => ['index', 'new', 'show', 'create', 'edit', 'update']]);
    $routes->get('tracking-pendientes/ruta/(:num)', 'TrackingController::getPendientesPorRuta/$1');
    $routes->get('tracking-pendientes/todos', 'TrackingController::getTodosPendientes');
    $routes->get('tracking-pendientes/rutas-con-paquetes/(:any)', 'TrackingController::rutasConPaquetes/$1');
    $routes->post('tracking/store', 'TrackingController::store');
    $routes->get('tracking-rendicion/(:num)', 'TrackingRendicionController::index/$1');
    $routes->post('tracking-rendicion/save', 'TrackingRendicionController::save');
    $routes->get('tracking-pdf/(:num)', 'TrackingRendicionController::pdf/$1');

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
    $routes->get('reports', 'ReportController::index');
});
