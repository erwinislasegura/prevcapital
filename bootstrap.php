<?php

declare(strict_types=1);

use App\Core\Config;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

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

require_once APP_ROOT . '/core/helpers.php';

Config::load('app', APP_ROOT . '/config/app.php');
Config::load('database', APP_ROOT . '/config/database.php');
date_default_timezone_set((string) Config::get('app.timezone', 'America/Santiago'));
