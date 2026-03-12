<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Dashboard principal pour les administrateurs
     */
    public function adminDashboard(): void
    {
        require base_path('html/back/dashboard/admin-dashboard.php');
    }

    /**
     * Dashboard pour les marchands/vendeurs
     */
    public function merchantDashboard(): void
    {
        require base_path('html/back/dashboard/marchant-dashboard.php');
    }

    /**
     * Dashboard pour les partenaires
     */
    public function partnerDashboard(): void
    {
        require base_path('html/back/dashboard/partener-dashboard.php');
    }

    /**
     * Dashboard simplifié par défaut
     */
    public function dashboard(): void
    {
        $this->adminDashboard();
    }
}
