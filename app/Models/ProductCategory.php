<?php

declare(strict_types=1);

namespace App\Models;

class ProductCategory extends BaseModel
{
    protected string $table = 'product_categories';

    protected array $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon_url',
        'is_active',
        'display_order',
    ];

    public function getParent(): ?self
    {
        if (!$this->parent_id) {
            return null;
        }
        return self::find($this->parent_id);
    }

    public function getChildren(): array
    {
        return self::query()->where('parent_id', $this->id)->orderBy('display_order')->get();
    }

    public function getProducts(): array
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::findBy('slug', $slug);
    }

    public static function getActiveRoots(): array
    {
        return self::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();
    }
}
