<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class Guest
{
    public function __invoke(Request $request, callable $next): Response
    {
        if (Auth::check()) {
            return Response::redirect('/');
        }

        return $next($request);
    }
}