<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;

class RbacMiddleware extends Middleware
{
    /** @param string[] $requiredRoles */
    public function __construct(private array $requiredRoles = [], private array $requiredPermissions = [])
    {
    }

    public function handle(): bool
    {
        /** @var ?User $user */
        $user = $GLOBALS['auth_user'] ?? null;
        if (!$user) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (str_starts_with($uri, '/admin')) {
                header('Location: /admin/login');
                exit;
            }
            $this->abort(['error' => 'Authentication required'], 401);
        }

        // Check roles
        if (!empty($this->requiredRoles)) {
            $userRoles = $user->getRoleNames();
            $hasRole = !empty(array_intersect($this->requiredRoles, $userRoles));
            if (!$hasRole) {
                $this->abort(['error' => 'Insufficient role'], 403);
            }
        }

        // Check permissions
        if (!empty($this->requiredPermissions)) {
            foreach ($this->requiredPermissions as $perm) {
                if (!$user->hasPermission($perm)) {
                    $this->abort(['error' => "Missing permission: {$perm}"], 403);
                }
            }
        }

        return true;
    }
}
