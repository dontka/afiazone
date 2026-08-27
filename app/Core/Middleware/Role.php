<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class Role
{
    public function __construct(private readonly string $role)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        if (! Auth::hasRole($this->role)) {
            return new Response('Acces interdit.', 403);
        }

        return $next($request);
    }
}