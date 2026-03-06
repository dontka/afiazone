<?php

declare(strict_types=1);

namespace App\Middleware;

class CorsMiddleware extends Middleware
{
    public function handle(): bool
    {
        $allowed = env('CORS_ALLOWED_ORIGINS', '*');
        header("Access-Control-Allow-Origin: {$allowed}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        return true;
    }
}
