<?php

declare(strict_types=1);

namespace App\Modules\Home\Controllers;

use App\Core\Controller;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->view('Home::index', [
            'title' => 'AfiaZone - Marketplace sante',
            'categories' => [
                ['name' => 'Medicaments', 'count' => '120 produits', 'tone' => 'mint'],
                ['name' => 'Diagnostic', 'count' => 'Tests et reactifs', 'tone' => 'blue'],
                ['name' => 'Protection', 'count' => 'Masques, gants', 'tone' => 'amber'],
                ['name' => 'Nutrition', 'count' => 'Vitamines, complements', 'tone' => 'rose'],
            ],
            'deals' => [
                ['name' => 'Paracetamol 500mg', 'seller' => 'Pharmacie pilote', 'price' => '2 500 CDF', 'badge' => 'Disponible'],
                ['name' => 'Test rapide paludisme', 'seller' => 'Laboratoire partenaire', 'price' => '6 800 CDF', 'badge' => 'Retrait rapide'],
                ['name' => 'Gants medicaux nitrile', 'seller' => 'Grossiste verifie', 'price' => '18 000 CDF', 'badge' => 'Stock suivi'],
            ],
            'benefits' => [
                'Vendeurs verifies',
                'Ordonnances controlees',
                'Livraison ou retrait',
                'Disponibilite locale',
            ],
            'metrics' => [
                '8' => 'categories sante',
                '24h' => 'objectif de retrait',
                '100%' => 'validation humaine ordonnance',
            ],
        ]);
    }
}