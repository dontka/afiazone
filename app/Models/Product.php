<?php

declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel
{
    protected string $table = 'products';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'merchant_id',
        'sku',
        'name',
        'slug',
        'description',
        'category_id',
        'price',
        'cost_price',
        'tax_rate',
        'prescription_required',
        'is_active',
        'is_featured',
        'status',
    ];

    public function getCategory(): ?BaseModel
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function getMerchant(): ?BaseModel
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function getImages(): array
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function getVariants(): array
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function getAttributes(): array
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }

    public function getReviews(): array
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public static function findBySku(string $sku): ?self
    {
        return self::findBy('sku', $sku);
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::findBy('slug', $slug);
    }

    public static function search(string $term, int $limit = 20): array
    {
        return self::query()
            ->fullTextSearch('name, description', $term)
            ->where('is_active', true)
            ->where('status', 'published')
            ->limit($limit)
            ->get();
    }

    public static function findByCategory(int $categoryId): array
    {
        return self::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
