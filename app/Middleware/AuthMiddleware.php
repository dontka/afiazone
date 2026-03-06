<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Models\User;

class AuthMiddleware extends Middleware
{
    public function handle(): bool
    {
        $token = $this->getBearerToken();

        if (!$token) {
            $this->abort(['error' => 'Missing authorization token'], 401);
        }

        $authService = new AuthService();
        $payload = $authService->validateToken($token);

        if (!$payload) {
            $this->abort(['error' => 'Invalid or expired token'], 401);
        }

        $user = User::find($payload['sub'] ?? 0);
        if (!$user || $user->status === 'banned') {
            $this->abort(['error' => 'Account not found or suspended'], 401);
        }

        // Store authenticated user in global request context
        $GLOBALS['auth_user'] = $user;
        $GLOBALS['auth_payload'] = $payload;

        return true;
    }
}
