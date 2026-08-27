<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class Authenticate
{
    public function __invoke(Request $request, callable $next): Response
    {
        if (! Auth::check()) {
            $returnTo = $request->path();
            if ($returnTo !== '/connexion' && $returnTo !== '/') {
                Session::put('_auth.intended', $returnTo);
            }

            return Response::redirect('/connexion?return_to=' . rawurlencode($returnTo));
        }

        return $next($request);
    }
}