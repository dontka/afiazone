<?php

declare(strict_types=1);

namespace App\Middleware;

class RateLimitMiddleware extends Middleware
{
    public function __construct(
        private int $maxRequests = 60,
        private int $windowInSeconds = 60
    ) {
    }

    public function handle(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $cacheDir = storage_path('cache');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . '/rl_' . md5($ip) . '.json';
        $now = time();

        $count = 1;
        $resetTime = $now + $this->windowInSeconds;

        if (file_exists($cacheFile)) {
            $data = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($data) && ($data['reset_time'] ?? 0) > $now) {
                $count = ($data['count'] ?? 0) + 1;
                $resetTime = $data['reset_time'];
            }
        }

        if ($count > $this->maxRequests) {
            header('Retry-After: ' . ($resetTime - $now));
            $this->abort(['error' => 'Too many requests'], 429);
        }

        file_put_contents($cacheFile, json_encode([
            'count' => $count,
            'reset_time' => $resetTime,
        ]), LOCK_EX);

        return true;
    }
}
