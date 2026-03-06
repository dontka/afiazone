<?php

declare(strict_types=1);

namespace App\Middleware;

class LoggingMiddleware extends Middleware
{
    public function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        logger()->info("HTTP {$method} {$path}", ['ip' => $ip]);

        return true;
    }
}
