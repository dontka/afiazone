<?php

declare(strict_types=1);

namespace App\Models;

class ProductVariant extends BaseModel
{
    protected string $table = 'product_variants';

    protected array $fillable = [
        'product_id',
        'sku_suffix',
        'variant_name',
        'variant_price',
        'stock_quantity',
    ];
}
