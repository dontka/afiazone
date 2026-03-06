<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;

class ProductService extends BaseService
{
    private ProductRepository $productRepo;

    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!empty($filters['search'])) {
            return $this->productRepo->search($filters['search'], $page, $perPage);
        }

        if (!empty($filters['category_id'])) {
            return $this->productRepo->findByCategory((int) $filters['category_id'], $page, $perPage);
        }

        return $this->productRepo->getPublished($page, $perPage);
    }

    public function getById(int $id): ?Product
    {
        /** @var ?Product */
        return $this->productRepo->find($id);
    }

    public function create(array $data, int $merchantId): Product
    {
        $errors = $this->validate($data, [
            'name' => 'required',
            'sku' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|numeric',
        ]);
        $this->throwIfErrors($errors);

        $slug = $this->generateSlug($data['name']);

        $product = Product::create([
            'merchant_id' => $merchantId,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'category_id' => $data['category_id'],
            'prescription_required' => $data['prescription_required'] ?? false,
            'is_active' => true,
            'status' => 'draft',
        ]);

        $this->log('Product created', ['product_id' => $product->id, 'merchant_id' => $merchantId]);
        return $product;
    }

    public function update(int $id, array $data): bool
    {
        $product = $this->productRepo->find($id);
        if (!$product) {
            throw new \App\Exceptions\NotFoundException('Product not found');
        }

        $result = $product->update($data);
        if ($result) {
            $this->log('Product updated', ['product_id' => $id]);
        }
        return $result;
    }

    public function delete(int $id): bool
    {
        $result = $this->productRepo->delete($id);
        if ($result) {
            $this->log('Product deleted', ['product_id' => $id]);
        }
        return $result;
    }

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        return $this->productRepo->search($query, $page, $perPage);
    }

    public function getFeatured(int $limit = 10): array
    {
        return $this->productRepo->getFeatured($limit);
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $existing = Product::findBySlug($slug);
        if ($existing) {
            $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }
        return $slug;
    }
}
