<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

class AccountController extends Controller
{
    public function customerDashboard(): Response
    {
        return $this->dashboard(false);
    }

    public function merchantDashboard(): Response
    {
        return $this->dashboard(true);
    }

    private function dashboard(bool $merchant): Response
    {
        return $this->view('Auth::account', [
            'title' => $merchant ? 'Espace marchand | AfiaZone' : 'Mon compte | AfiaZone',
            'merchant' => $merchant,
            'user' => Auth::user(),
        ], $merchant ? 'merchant' : 'client');
    }
}