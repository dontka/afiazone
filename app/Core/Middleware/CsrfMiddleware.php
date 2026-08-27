<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (! Csrf::validate((string) $request->input('_csrf', ''))) {
                return new Response('Jeton CSRF invalide.', 419);
            }
        }

        return $next($request);
    }
}