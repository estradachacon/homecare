<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], function($routes) {    // Grupo del Dashboard (requiere autenticación)
    $routes->get('/dashboard', 'DashboardController::index'); // Página principal del dashboard
    
    // Módulo de Pedidos
    $routes->group('orders', function($routes) {
        $routes->resource('orders', [
            'controller' => 'OrderController'
        ]);
        // Rutas adicionales
        $routes->get('(:num)/invoice', 'OrderController::invoice/$1');
        $routes->post('(:num)/cancel', 'OrderController::cancel/$1');
    });
    
    // Módulo de Reportes
    $routes->group('reports', function($routes) {
        $routes->get('sales', 'ReportController::sales');
        $routes->get('users', 'ReportController::users');
        $routes->post('generate', 'ReportController::generate');
    }); 
    // Mantenimientos de cajas
    $routes->presenter('cashiers', ['controller' => 'CashierController', 'only' => ['index', 'show', 'new', 'create', 'edit', 'update']]);
    $routes->post('cashiers/delete', 'CashierController::delete');
    // Módulo de mantenimiento de usuarios
    $routes->presenter('users', ['controller' => 'UserController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);
    // Módulo de mantenimiento de sucursales
    $routes->presenter('branches', ['controller' => 'BranchController', 'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']]);
    // Mantenimiento de sistema
    $routes->presenter('settings', ['controller' => 'SettingsController', 'only' => ['index', 'update']]);
    $routes->get('logs', 'BitacoraController::index');
});