<?php

declare(strict_types=1);

use App\Core\Config;

require __DIR__ . '/bootstrap.php';

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
