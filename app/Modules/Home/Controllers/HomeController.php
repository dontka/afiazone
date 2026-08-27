<?php

declare(strict_types=1);

namespace App\Modules\Home\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;
use App\Modules\Catalog\Services\CatalogService;

class HomeController extends Controller
{
    public function index(): Response
    {
        $catalog = new CatalogService();
        $catalogCategories = $catalog->categories();
        $categoryStyles = [
            'mint' => ['icon' => 'M', 'tone' => 'mint'],
            'blue' => ['icon' => 'D', 'tone' => 'blue'],
            'amber' => ['icon' => 'P', 'tone' => 'amber'],
            'rose' => ['icon' => 'N', 'tone' => 'rose'],
            'teal' => ['icon' => 'S', 'tone' => 'teal'],
        ];
        $categories = [];
        foreach (array_slice($catalogCategories, 0, 5) as $index => $category) {
            $style = $categoryStyles[array_keys($categoryStyles)[$index] ?? 'mint'];
            $categories[] = [
                'name' => $category['name'],
                'slug' => $category['slug'],
                'count' => (int) $category['product_count'] . ' produit(s)',
                'icon' => $style['icon'],
                'tone' => $style['tone'],
            ];
        }
        $featuredProducts = [];
        foreach (array_slice($catalog->products(), 0, 8) as $index => $product) {
            $tones = ['green', 'blue', 'amber', 'rose', 'dark', 'mint', 'violet', 'orange'];
            $featuredProducts[] = [
                'name' => $product['name'],
                'slug' => $product['slug'],
                'price' => null,
                'unit' => '',
                'tag' => (int) $product['requires_prescription'] === 1 ? 'Ordonnance' : 'Catalogue',
                'meta' => $product['short_description'] ?: $product['category_name'],
                'tone' => $tones[$index] ?? 'green',
                'requires_prescription' => (int) $product['requires_prescription'],
            ];
        }
        $brands = $catalog->brands();

        return $this->view('Home::index', [
            'title' => 'AfiaZone | Marketplace santé & maison',
            'categories' => $categories,
            'brands' => $brands,
            'stores' => [
                ['name' => 'Pharmacie centrale', 'location' => 'Centre-ville', 'distance' => '0,8 km', 'tone' => 'blue'],
                ['name' => 'Afia Market', 'location' => 'Gombe', 'distance' => '1,2 km', 'tone' => 'green'],
                ['name' => 'City Care Store', 'location' => 'Kintambo', 'distance' => '2,4 km', 'tone' => 'orange'],
                ['name' => 'MediPlus', 'location' => 'Limete', 'distance' => '3,1 km', 'tone' => 'rose'],
                ['name' => 'Wellness Hub', 'location' => 'Ngaliema', 'distance' => '4,3 km', 'tone' => 'purple'],
            ],
            'featuredProducts' => $featuredProducts,
            'isAuthenticated' => Auth::check(),
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
            'layoutChrome' => false,
        ]);
    }
}