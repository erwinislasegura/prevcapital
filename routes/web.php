<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\RoleController;
use App\Controllers\SetupController;
use App\Controllers\UserController;
use App\Core\Router;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'show']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/setup', [SetupController::class, 'show']);
$router->post('/setup', [SetupController::class, 'store']);

$router->get('/admin', [DashboardController::class, 'index'], ['auth', 'permission:dashboard.view']);
$router->get('/admin/users', [UserController::class, 'index'], ['auth', 'permission:users.view']);
$router->get('/admin/users/create', [UserController::class, 'create'], ['auth', 'permission:users.create']);
$router->post('/admin/users/create', [UserController::class, 'store'], ['auth', 'permission:users.create']);
$router->get('/admin/users/edit', [UserController::class, 'edit'], ['auth', 'permission:users.edit']);
$router->post('/admin/users/edit', [UserController::class, 'update'], ['auth', 'permission:users.edit']);
$router->post('/admin/users/toggle', [UserController::class, 'toggle'], ['auth', 'permission:users.edit']);
$router->post('/admin/users/delete', [UserController::class, 'delete'], ['auth', 'permission:users.delete']);

$router->get('/admin/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.view']);
$router->get('/admin/roles/create', [RoleController::class, 'create'], ['auth', 'permission:roles.create']);
$router->post('/admin/roles/create', [RoleController::class, 'store'], ['auth', 'permission:roles.create']);
$router->get('/admin/roles/edit', [RoleController::class, 'edit'], ['auth', 'permission:roles.edit']);
$router->post('/admin/roles/edit', [RoleController::class, 'update'], ['auth', 'permission:roles.edit']);
$router->post('/admin/roles/delete', [RoleController::class, 'delete'], ['auth', 'permission:roles.delete']);

return $router;
