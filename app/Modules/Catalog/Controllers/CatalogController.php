<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Controllers;

use App\Core\Controller;
use App\Core\FileStorage;
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
        if ($service->findCategory($slug) === null) {
            return new Response('Categorie introuvable.', 404);
        }
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
            'categories' => $service->categories(true),
            'products' => $service->adminProducts(),
            'brands' => $service->brandOptions(),
            'ingredients' => $service->ingredients(),
            'errors' => Session::consumeFlash('catalog.errors', []),
            'message' => Session::consumeFlash('catalog.message'),
        ], 'admin');
    }

    public function storeCategory(): Response
    {
        $request = Request::capture();
        $data = [
            'name' => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
            'parent_id' => $request->input('parent_id'),
            'status' => (string) $request->input('status', 'draft'),
        ];
        $validator = new Validator($data);
        if (! $validator->validate(['name' => 'required|string|min:2|max:160', 'status' => 'required|in:draft,published,archived'])) {
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
            'brand_id' => $request->input('brand_id'),
            'ingredient_ids' => array_map('intval', (array) $request->input('ingredient_ids', [])),
            'status' => (string) $request->input('status', 'pending_review'),
        ];
        $validator = new Validator($data);
        if (! $validator->validate([
            'name' => 'required|string|min:2|max:190',
            'category_slug' => 'required|string|max:190',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,pending_review,published,archived',
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

    public function adminCategories(): Response
    {
        return $this->adminIndex();
    }

    public function adminProducts(): Response
    {
        return $this->adminIndex();
    }

    public function updateCategory(string $id): Response
    {
        $request = Request::capture();
        $data = [
            'name' => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
            'parent_id' => $request->input('parent_id'),
            'status' => (string) $request->input('status', 'draft'),
        ];
        $validator = new Validator($data);
        if (! $validator->validate(['name' => 'required|string|min:2|max:160', 'status' => 'required|in:draft,published,archived'])) {
            Session::flash('catalog.errors', $validator->errors());
            return $this->redirect('/admin/categories');
        }
        try {
            (new CatalogService())->updateCategory((int) $id, $data);
            Session::flash('catalog.message', 'Categorie mise a jour.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['category' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/categories');
    }

    public function deleteCategory(string $id): Response
    {
        try {
            (new CatalogService())->deleteCategory((int) $id);
            Session::flash('catalog.message', 'Categorie supprimee.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['category' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/categories');
    }

    public function editProduct(string $id): Response
    {
        $product = (new CatalogService())->findAdminProduct((int) $id);
        if ($product === null) {
            return new Response('Produit introuvable.', 404);
        }
        $service = new CatalogService();
        return $this->view('Catalog::admin/edit', [
            'title' => 'Modifier le produit | AfiaZone',
            'product' => $product,
            'categories' => $service->categories(true),
            'brands' => $service->brandOptions(),
            'ingredients' => $service->ingredients(),
            'errors' => Session::consumeFlash('catalog.errors', []),
        ], 'admin');
    }

    public function updateProduct(string $id): Response
    {
        $request = Request::capture();
        $data = $this->productData($request);
        $validator = new Validator($data);
        if (! $validator->validate([
            'name' => 'required|string|min:2|max:190',
            'category_id' => 'required|integer',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,pending_review,published,archived',
        ])) {
            Session::flash('catalog.errors', $validator->errors());
            return $this->redirect('/admin/produits/' . (int) $id . '/modifier');
        }
        try {
            (new CatalogService())->updateProduct((int) $id, $data);
            Session::flash('catalog.message', 'Produit mis a jour.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['product' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/produits/' . (int) $id . '/modifier');
    }

    public function deleteProduct(string $id): Response
    {
        try {
            $paths = (new CatalogService())->deleteProduct((int) $id);
            $this->deleteStoredFiles($paths);
            Session::flash('catalog.message', 'Produit supprime.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['product' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/produits');
    }

    public function changeProductStatus(string $id): Response
    {
        $status = (string) Request::capture()->input('status', '');
        try {
            (new CatalogService())->changeProductStatus((int) $id, $status);
            Session::flash('catalog.message', 'Statut du produit mis a jour.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['status' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/produits');
    }

    public function storeImage(string $id): Response
    {
        try {
            $path = $this->storage()->storeUploadedFile(Request::capture()->file('image') ?? [], 'products/images', ['image/jpeg', 'image/png', 'image/webp']);
            (new CatalogService())->addImage((int) $id, $path, trim((string) Request::capture()->input('alt_text', '')) ?: null);
            Session::flash('catalog.message', 'Image ajoutee.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['image' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/produits/' . (int) $id . '/modifier');
    }

    public function storeDocument(string $id): Response
    {
        try {
            $path = $this->storage()->storeUploadedFile(Request::capture()->file('document') ?? [], 'products/documents', ['application/pdf', 'image/jpeg', 'image/png']);
            (new CatalogService())->addDocument((int) $id, $path, trim((string) Request::capture()->input('document_type', 'fiche')) ?: 'fiche');
            Session::flash('catalog.message', 'Document ajoute.');
        } catch (Throwable $exception) {
            Session::flash('catalog.errors', ['document' => [$exception->getMessage()]]);
        }
        return $this->redirect('/admin/produits/' . (int) $id . '/modifier');
    }

    private function productData(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'short_description' => trim((string) $request->input('short_description', '')),
            'description' => trim((string) $request->input('description', '')),
            'requires_prescription' => $request->input('requires_prescription', false),
            'ingredient_ids' => array_map('intval', (array) $request->input('ingredient_ids', [])),
            'status' => (string) $request->input('status', 'pending_review'),
            'variants' => [],
        ];
    }

    private function storage(): FileStorage
    {
        return new FileStorage(dirname(__DIR__, 4) . '/storage/uploads');
    }

    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $fullPath = dirname(__DIR__, 4) . '/storage/uploads/' . ltrim($path, '/\\');
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}