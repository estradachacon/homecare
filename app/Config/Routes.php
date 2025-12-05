<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

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
        $routes->get('sales', 'ReportController::sales');
        $routes->get('users', 'ReportController::users');
        $routes->post('generate', 'ReportController::generate');
    });
    // Mantenimientos de cajas
    $routes->presenter('cashiers', ['controller' => 'CashierController', 'only' => ['index', 'show', 'new', 'create', 'edit', 'update']]);
    $routes->post('cashiers/delete', 'CashierController::delete');

    // Módulo de mantenimiento de usuarios
    $routes->presenter('users', ['controller' => 'UserController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('users/delete', 'UserController::delete');

    // Módulo de mantenimiento de sucursales
    $routes->get('branches-list', 'BranchController::list');
    $routes->presenter('branches', ['controller' => 'BranchController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);

    // Mantenimiento de sistema
    $routes->presenter('settings', ['controller' => 'SettingsController', 'only' => ['index', 'update']]);
    $routes->get('logs', 'BitacoraController::index');
    $routes->presenter('packages', ['controller' => 'PackageController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete', 'show']]);

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
    $routes->post('packages-devolver/(:num)', 'PackageController::devolver/$1');
    $routes->get('packages-getDestinoInfo/(:num)', 'PackageController::getDestinoInfo/$1');

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
});
