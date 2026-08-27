<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class Permission
{
    public function __construct(private readonly string $permission)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        if (! Auth::hasPermission($this->permission)) {
            return new Response('Acces interdit.', 403);
        }

        return $next($request);
    }
}