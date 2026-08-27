<?php

declare(strict_types=1);

return [
    'mailer' => env('MAIL_MAILER', 'smtp'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => (int) env('MAIL_PORT', 1025),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'none'),
    'timeout' => (int) env('MAIL_TIMEOUT', 10),
    'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@afyazone.test'),
    'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'AfiaZone')),
];