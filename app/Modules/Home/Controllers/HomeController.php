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
            'title' => 'AfiaZone | Marketplace santé & maison',
            'categories' => [
                ['name' => 'Medicaments', 'count' => '120 produits', 'icon' => '💊', 'tone' => 'mint'],
                ['name' => 'Diagnostic', 'count' => 'Tests rapides', 'icon' => '🧪', 'tone' => 'blue'],
                ['name' => 'Protection', 'count' => 'Masques & gants', 'icon' => '🧤', 'tone' => 'amber'],
                ['name' => 'Nutrition', 'count' => 'Vitamines', 'icon' => '🥗', 'tone' => 'rose'],
                ['name' => 'Soins', 'count' => 'Beauté & hygiene', 'icon' => '🧴', 'tone' => 'teal'],
            ],
            'brands' => ['Pharmacie', 'MediPlus', 'WellCare', 'HealthMart', 'LabOne', 'Carex'],
            'stores' => [
                ['name' => 'Pharmacie centrale', 'location' => 'Centre-ville', 'distance' => '0,8 km', 'tone' => 'blue'],
                ['name' => 'Afia Market', 'location' => 'Gombe', 'distance' => '1,2 km', 'tone' => 'green'],
                ['name' => 'City Care Store', 'location' => 'Kintambo', 'distance' => '2,4 km', 'tone' => 'orange'],
                ['name' => 'MediPlus', 'location' => 'Limete', 'distance' => '3,1 km', 'tone' => 'rose'],
                ['name' => 'Wellness Hub', 'location' => 'Ngaliema', 'distance' => '4,3 km', 'tone' => 'purple'],
            ],
            'featuredProducts' => [
                ['name' => 'Paracetamol 500mg', 'price' => '2 500', 'unit' => 'CDF', 'tag' => 'Promo', 'meta' => 'Pharmacie pilote', 'tone' => 'green'],
                ['name' => 'Test rapide paludisme', 'price' => '6 800', 'unit' => 'CDF', 'tag' => 'En stock', 'meta' => 'Laboratoire', 'tone' => 'blue'],
                ['name' => 'Gants nitrile', 'price' => '18 000', 'unit' => 'CDF', 'tag' => 'Top vente', 'meta' => 'Protection', 'tone' => 'amber'],
                ['name' => 'Vitamine C 1000mg', 'price' => '4 900', 'unit' => 'CDF', 'tag' => 'Sante', 'meta' => 'Nutrition', 'tone' => 'rose'],
                ['name' => 'Tensiomètre digital', 'price' => '29 000', 'unit' => 'CDF', 'tag' => 'Nouvel arrivage', 'meta' => 'Diagnostic', 'tone' => 'dark'],
                ['name' => 'Gel hydroalcoolique', 'price' => '3 200', 'unit' => 'CDF', 'tag' => 'Confiance', 'meta' => 'Hygiène', 'tone' => 'mint'],
                ['name' => 'Complément immunité', 'price' => '7 500', 'unit' => 'CDF', 'tag' => 'Recommandé', 'meta' => 'Nutrition', 'tone' => 'violet'],
                ['name' => 'Masque N95', 'price' => '9 000', 'unit' => 'CDF', 'tag' => 'Stock sûr', 'meta' => 'Protection', 'tone' => 'orange'],
            ],
            'collections' => [
                [
                    'title' => 'Home Cleaning',
                    'subtitle' => 'Hygiène douce',
                    'items' => [
                        ['name' => 'Nettoyant multi-usage', 'price' => '4 200', 'tag' => 'Top', 'tone' => 'mint'],
                        ['name' => 'Lessive concentrée', 'price' => '5 650', 'tag' => 'Promo', 'tone' => 'blue'],
                        ['name' => 'Mop & serpillière', 'price' => '12 900', 'tag' => 'New', 'tone' => 'amber'],
                        ['name' => 'Désinfectant maison', 'price' => '6 800', 'tag' => 'Populaire', 'tone' => 'rose'],
                    ],
                ],
                [
                    'title' => 'Wellness & nutrition',
                    'subtitle' => 'Pour une vie plus saine',
                    'items' => [
                        ['name' => 'Boisson énergisante', 'price' => '3 950', 'tag' => 'Boost', 'tone' => 'violet'],
                        ['name' => 'Mélange superfoods', 'price' => '8 700', 'tag' => 'Best', 'tone' => 'green'],
                        ['name' => 'Énergie naturelle', 'price' => '6 100', 'tag' => 'Nouveau', 'tone' => 'teal'],
                        ['name' => 'Supplément anti-stress', 'price' => '9 300', 'tag' => 'Top', 'tone' => 'orange'],
                    ],
                ],
            ],
            'benefits' => [
                ['title' => 'Livraison rapide', 'text' => 'Sous 24h dans les zones couvertes'],
                ['title' => 'Paiement sécurisé', 'text' => 'Mobile money & paiement à la livraison'],
                ['title' => 'Conseils fiables', 'text' => 'Validation humaine pour les produits sensibles'],
            ],
            'stats' => [
                ['value' => '8k+', 'label' => 'Produits recherchés'],
                ['value' => '24h', 'label' => 'Retrait ou livraison'],
                ['value' => '4.9/5', 'label' => 'Satisfaction vendeurs'],
            ],
        ]);
    }
}