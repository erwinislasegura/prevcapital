<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'PrevCapital',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'base_url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Santiago',
];
