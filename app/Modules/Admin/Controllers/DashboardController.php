<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Core\Controller;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return $this->view('Admin::dashboard', [
            'title' => 'Dashboard AfiaZone',
            'stats' => [
                ['label' => 'Modules actifs', 'value' => '3', 'hint' => 'Home, Admin, System'],
                ['label' => 'Routes validees', 'value' => '4', 'hint' => '200, 200, 200, 404'],
                ['label' => 'Sprint courant', 'value' => '1', 'hint' => 'Socle MVC'],
            ],
            'queues' => [
                'Catalogue medical' => 'Prochain module apres Auth et RBAC.',
                'KYC marchands' => 'Preparera les vendeurs verifies.',
                'Stock disponible' => 'Necessaire avant panier et commande.',
            ],
        ], 'admin');
    }
}