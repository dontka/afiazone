<?php

declare(strict_types=1);

namespace App\Models;

class ProductImage extends BaseModel
{
    protected string $table = 'product_images';

    protected array $fillable = [
        'product_id',
        'image_url',
        'alt_text',
        'is_primary',
        'display_order',
    ];
}
