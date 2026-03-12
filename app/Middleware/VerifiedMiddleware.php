<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;

class VerifiedMiddleware extends Middleware
{
    public function handle(): bool
    {
        /** @var ?User $user */
        $user = $GLOBALS['auth_user'] ?? null;
        if (!$user) {
            $this->abort(['error' => 'Authentication required'], 401);
        }

        if (empty($user->email_verified_at)) {
            $this->abort(['error' => 'Email verification required'], 403);
        }

        return true;
    }
}
