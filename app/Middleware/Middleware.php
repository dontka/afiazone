<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Base Middleware — return true to continue, false to halt pipeline.
 */
abstract class Middleware
{
    abstract public function handle(): bool;

    protected function getHeader(string $name): ?string
    {
        $headerName = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$headerName] ?? null;
    }

    protected function getBearerToken(): ?string
    {
        $header = $this->getHeader('Authorization');
        if ($header && preg_match('/Bearer\s+(.+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function abort(array $data, int $statusCode = 400): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
