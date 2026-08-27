<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Modules\Catalog\Services\CatalogService;
use Throwable;

class CatalogController extends Controller
{
    public function index(): Response
    {
        $request = Request::capture();
        $service = new CatalogService();
        return $this->view('Catalog::index', [
            'title' => 'Catalogue santé | AfiaZone',
            'products' => $service->products((string) $request->query('q', ''), (string) $request->query('category', '')),
            'categories' => $service->categories(),
            'search' => (string) $request->query('q', ''),
            'selectedCategory' => (string) $request->query('category', ''),
        ]);
    }

    public function category(string $slug): Response
    {
        $service = new CatalogService();
        return $this->view('Catalog::index', [
            'title' => 'Catalogue par categorie | AfiaZone',
            'products' => $service->products(null, $slug),
            'categories' => $service->categories(),
            'search' => '',
            'selectedCategory' => $slug,
        ]);
    }

    public function show(string $slug): Response
    {
        $product = (new CatalogService())->findBySlug($slug);
        if ($product === null) {
            return new Response('Produit introuvable.', 404);
        }
        return $this->view('Catalog::show', [
            'title' => $product['name'] . ' | AfiaZone',
            'product' => $product,
        ]);
    }

    public function adminIndex(): Response
    {
        $service = new CatalogService();
        return $this->view('Catalog::admin/index', [
            'title' => 'Gestion du catalogue | AfiaZone',
            'categories' => $service->categories(),
            'products' => $service->products(),
            'errors' => Session::consumeFlash('catalog.errors', []),
            'message' => Session::consumeFlash('catalog.message'),
        ], 'admin');
    }

    public function storeCategory(): Response
    {
        $data = ['name' => trim((string) Request::capture()->input('name', '')), 'description' => trim((string) Request::capture()->input('description', ''))];
        $validator = new Validator($data);
        if (! $validator->validate(['name' => 'required|string|min:2|max:160'])) {
            Session::flash('catalog.errors', $validator->errors());
            return $this->redirect('/admin/catalogue');
        }
        try {
            (new CatalogService())->createCategory($data);
        } catch (Throwable) {
            Session::flash('catalog.errors', ['name' => ['Cette categorie existe deja ou est invalide.']]);
            return $this->redirect('/admin/catalogue');
        }
        Session::flash('catalog.message', 'Categorie creee.');
        return $this->redirect('/admin/catalogue');
    }

    public function storeProduct(): Response
    {
        $request = Request::capture();
        $data = [
            'name' => trim((string) $request->input('name', '')),
            'category_slug' => trim((string) $request->input('category_slug', '')),
            'short_description' => trim((string) $request->input('short_description', '')),
            'description' => trim((string) $request->input('description', '')),
            'requires_prescription' => $request->input('requires_prescription', false),
        ];
        $validator = new Validator($data);
        if (! $validator->validate([
            'name' => 'required|string|min:2|max:190',
            'category_slug' => 'required|string|max:190',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
        ])) {
            Session::flash('catalog.errors', $validator->errors());
            return $this->redirect('/admin/catalogue');
        }
        try {
            (new CatalogService())->createProduct($data);
        } catch (Throwable) {
            Session::flash('catalog.errors', ['name' => ['Le produit ou sa categorie est invalide.']]);
            return $this->redirect('/admin/catalogue');
        }
        Session::flash('catalog.message', 'Produit publie.');
        return $this->redirect('/admin/catalogue');
    }
}