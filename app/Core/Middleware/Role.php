<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class Role
{
    public function __construct(private readonly string|array $role)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        $allowed = is_array($this->role) ? Auth::hasAnyRole($this->role) : Auth::hasRole($this->role);
        if (! $allowed) {
            return new Response('Acces interdit.', 403);
        }

        return $next($request);
    }
}