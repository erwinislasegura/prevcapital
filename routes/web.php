<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PublicQuoteController;
use App\Controllers\QuoteController;
use App\Controllers\RoleController;
use App\Controllers\SetupController;
use App\Controllers\UserController;
use App\Core\Router;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/servicios', [HomeController::class, 'services']);
$router->get('/cumplimiento', [HomeController::class, 'compliance']);
$router->get('/nosotros', [HomeController::class, 'about']);
$router->get('/contacto', [HomeController::class, 'contact']);
$router->post('/contacto', [HomeController::class, 'contactStore']);
$router->get('/cotizacion', [PublicQuoteController::class, 'show']);
$router->get('/cotizacion/pdf', [PublicQuoteController::class, 'pdf']);
$router->post('/cotizacion/responder', [PublicQuoteController::class, 'respond']);
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

$router->get('/admin/contactos', [ContactController::class, 'index'], ['auth', 'permission:contacts.view']);
$router->get('/admin/contactos/ver', [ContactController::class, 'show'], ['auth', 'permission:contacts.view']);
$router->post('/admin/contactos/estado', [ContactController::class, 'status'], ['auth', 'permission:contacts.manage']);
$router->post('/admin/contactos/eliminar', [ContactController::class, 'delete'], ['auth', 'permission:contacts.delete']);

$router->get('/admin/cotizaciones', [QuoteController::class, 'index'], ['auth', 'permission:quotes.view']);
$router->get('/admin/cotizaciones/crear', [QuoteController::class, 'create'], ['auth', 'permission:quotes.create']);
$router->post('/admin/cotizaciones/crear', [QuoteController::class, 'store'], ['auth', 'permission:quotes.create']);
$router->get('/admin/cotizaciones/ver', [QuoteController::class, 'show'], ['auth', 'permission:quotes.view']);
$router->get('/admin/cotizaciones/editar', [QuoteController::class, 'edit'], ['auth', 'permission:quotes.edit']);
$router->post('/admin/cotizaciones/editar', [QuoteController::class, 'update'], ['auth', 'permission:quotes.edit']);
$router->get('/admin/cotizaciones/pdf', [QuoteController::class, 'pdf'], ['auth', 'permission:quotes.view']);
$router->post('/admin/cotizaciones/enviar', [QuoteController::class, 'send'], ['auth', 'permission:quotes.send']);
$router->post('/admin/cotizaciones/eliminar', [QuoteController::class, 'delete'], ['auth', 'permission:quotes.delete']);

return $router;
