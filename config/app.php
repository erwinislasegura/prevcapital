<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'PrevCapital',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'base_url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Santiago',
    'contact' => [
        'email' => getenv('CONTACT_EMAIL') ?: 'contacto@prevcapital.cl',
        'phone_primary' => getenv('CONTACT_PHONE_PRIMARY') ?: '+56 9 6418 0365',
        'phone_secondary' => getenv('CONTACT_PHONE_SECONDARY') ?: '+56 9 8597 4082',
        'location' => getenv('CONTACT_LOCATION') ?: 'La Serena, Chile',
        'coverage' => getenv('CONTACT_COVERAGE') ?: 'Región de Coquimbo, Chile',
    ],
    'social' => [
        'instagram' => getenv('SOCIAL_INSTAGRAM_URL') ?: '',
        'facebook' => getenv('SOCIAL_FACEBOOK_URL') ?: '',
        'linkedin' => getenv('SOCIAL_LINKEDIN_URL') ?: '',
    ],
    'mail' => [
        'transport' => getenv('MAIL_TRANSPORT') ?: 'mail',
        'host' => getenv('MAIL_HOST') ?: '',
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'timeout' => (int) (getenv('MAIL_TIMEOUT') ?: 20),
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'contacto@prevcapital.cl',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'PrevCapital',
        'reply_to' => getenv('MAIL_REPLY_TO') ?: 'contacto@prevcapital.cl',
        'notification_address' => getenv('MAIL_NOTIFICATION_ADDRESS') ?: 'contacto@prevcapital.cl',
        'dkim_selector' => getenv('MAIL_DKIM_SELECTOR') ?: 'default',
    ],
];
