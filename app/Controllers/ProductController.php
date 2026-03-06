<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProductService;

class ProductController extends BaseController
{
    private ProductService $productService;

    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
    }

    public function index(): void
    {
        $page = (int) ($this->getData('page') ?? 1);
        $perPage = (int) ($this->getData('per_page') ?? 20);
        $filters = [
            'search' => $this->getData('search'),
            'category_id' => $this->getData('category_id'),
        ];

        $result = $this->productService->getAll($filters, $page, $perPage);
        $this->jsonResponse($result);
    }

    public function show(int $id): void
    {
        $product = $this->productService->getById($id);

        if (!$product) {
            $this->errorResponse('Product not found', 404);
            return;
        }

        $this->jsonResponse(['product' => $product->toArray()]);
    }

    public function store(): void
    {
        $this->authorize('products.create');
        $product = $this->productService->create($this->getData(), $this->authUserId());
        $this->jsonResponse(['product' => $product->toArray()], 201);
    }

    public function update(int $id): void
    {
        $this->authorize('products.update');
        $this->productService->update($id, $this->getData());
        $product = $this->productService->getById($id);
        $this->jsonResponse(['product' => $product?->toArray() ?? []]);
    }

    public function destroy(int $id): void
    {
        $this->authorize('products.delete');
        $this->productService->delete($id);
        $this->jsonResponse(['message' => 'Product deleted']);
    }

    public function featured(): void
    {
        $products = $this->productService->getFeatured();
        $this->jsonResponse(['products' => array_map(fn($p) => $p->toArray(), $products)]);
    }
}
