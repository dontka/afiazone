<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'AfiaZone'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://afyazone.test'),
    'timezone' => env('APP_TIMEZONE', 'Africa/Kinshasa'),
    'modules' => [
        'Home',
        'Admin',
        'System',
    ],
    'database' => require __DIR__ . '/database.php',
];