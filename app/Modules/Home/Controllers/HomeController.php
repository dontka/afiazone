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
        $allProducts = $catalog->products();
        $tones = ['mint', 'blue', 'amber', 'rose', 'teal'];
        $collections = [];
        foreach (array_slice($catalogCategories, 0, 2) as $categoryIndex => $category) {
            $categoryProducts = array_values(array_filter(
                $allProducts,
                static fn (array $product): bool => $product['category_slug'] === $category['slug']
            ));
            $items = [];
            foreach (array_slice($categoryProducts, 0, 4) as $itemIndex => $product) {
                $items[] = [
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'tag' => (int) $product['requires_prescription'] === 1 ? 'Ordonnance' : 'Catalogue',
                    'tone' => $tones[($categoryIndex + $itemIndex) % count($tones)],
                ];
            }
            if ($items !== []) {
                $collections[] = [
                    'title' => $category['name'],
                    'subtitle' => $category['description'] ?: 'Produits disponibles',
                    'items' => $items,
                ];
            }
        }

        return $this->view('Home::index', [
            'title' => 'AfiaZone | Marketplace santé & maison',
            'categories' => $categories,
            'brands' => $brands,
            'featuredProducts' => $featuredProducts,
            'isAuthenticated' => Auth::check(),
            'collections' => $collections,
            'stats' => [
                ['value' => (string) $catalog->publishedProductCount(), 'label' => 'Produits publiés'],
                ['value' => (string) count($catalogCategories), 'label' => 'Catégories actives'],
                ['value' => (string) $catalog->brandCount(), 'label' => 'Marques référencées'],
            ],
            'layoutChrome' => false,
        ]);
    }
}