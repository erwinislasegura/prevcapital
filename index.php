<?php

declare(strict_types=1);

use App\Core\Config;

define('APP_ROOT', __DIR__);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);
    $top = array_shift($parts);
    $directories = [
        'Core' => APP_ROOT . '/core',
        'Controllers' => APP_ROOT . '/app/Controllers',
        'Models' => APP_ROOT . '/app/Models',
        'Support' => APP_ROOT . '/app/Support',
    ];
    if (!isset($directories[$top])) {
        return;
    }
    $path = $directories[$top] . '/' . implode('/', $parts) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require APP_ROOT . '/core/helpers.php';

Config::load('app', APP_ROOT . '/config/app.php');
Config::load('database', APP_ROOT . '/config/database.php');
date_default_timezone_set((string) Config::get('app.timezone', 'America/Santiago'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('prevcapital_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

$_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
unset($_SESSION['_flash_new']);

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$router = require APP_ROOT . '/routes/web.php';
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    error_log($exception->__toString());
    if (Config::get('app.debug')) {
        throw $exception;
    }
    http_response_code(500);
    \App\Core\View::render('errors/500', [], 'public');
}
