<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Product());
    }

    public function findBySku(string $sku): ?Product
    {
        /** @var ?Product */
        return $this->findBy('sku', $sku);
    }

    public function findBySlug(string $slug): ?Product
    {
        /** @var ?Product */
        return $this->findBy('slug', $slug);
    }

    public function findByCategory(int $categoryId, int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }

    public function findByMerchant(int $merchantId, int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->where('merchant_id', $merchantId)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }

    public function findPrescriptionRequired(): array
    {
        return $this->query()
            ->where('prescription_required', true)
            ->where('is_active', true)
            ->get();
    }

    public function search(string $term, int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->fullTextSearch('name, description', $term)
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('rating', 'DESC')
            ->paginate($page, $perPage);
    }

    public function getPublished(int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }

    public function getFeatured(int $limit = 10): array
    {
        return $this->query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('rating', 'DESC')
            ->limit($limit)
            ->get();
    }
}
