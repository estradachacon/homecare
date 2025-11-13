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
    $routes->presenter('branches', ['controller' => 'BranchController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);
    // Mantenimiento de sistema
    $routes->presenter('settings', ['controller' => 'SettingsController', 'only' => ['index', 'update']]);
    $routes->get('logs', 'BitacoraController::index');
    $routes->presenter('packages', ['controller' => 'PackageController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);
    $routes->presenter('sellers', ['controller' => 'SellerController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->get('sellers/search', 'SellerController::search');
    $routes->post('sellers/delete', 'SellerController::delete');
    $routes->post('sellers/create-ajax', 'SellerController::createAjax');
    $routes->presenter('settledpoint', ['controller' => 'SettledPointController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('settledpoint/delete', 'SettledPointController::delete');
    $routes->get('settledPoints/getList', 'SettledPointController::getList');
    $routes->get('settledPoints/getDays/(:num)', 'SettledPointController::getAvailableDays/$1');
    $routes->presenter('routes', ['controller' => 'RouteController', 'only' => ['index', 'new', 'create', 'edit', 'update']]);
    $routes->post('routes/delete', 'RouteController::delete');
});
