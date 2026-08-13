<?php

declare(strict_types=1);

$configuration = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'prevcapital',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

$localPath = __DIR__ . '/database.local.php';
if (is_file($localPath)) {
    $configuration = array_replace($configuration, require $localPath);
}

$environmentMap = [
    'host' => 'DB_HOST', 'port' => 'DB_PORT', 'database' => 'DB_DATABASE',
    'username' => 'DB_USERNAME', 'password' => 'DB_PASSWORD',
];
foreach ($environmentMap as $key => $variable) {
    if (getenv($variable) !== false) {
        $configuration[$key] = getenv($variable);
    }
}

return $configuration;
