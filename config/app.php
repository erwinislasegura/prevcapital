<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'PrevCapital',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'base_url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Santiago',
    'mail' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'contacto@prevcapital.cl',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'PrevCapital',
        'reply_to' => getenv('MAIL_REPLY_TO') ?: 'contacto@prevcapital.cl',
        'notification_address' => getenv('MAIL_NOTIFICATION_ADDRESS') ?: 'contacto@prevcapital.cl',
    ],
];
