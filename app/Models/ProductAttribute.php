<?php

declare(strict_types=1);

namespace App\Models;

class ProductAttribute extends BaseModel
{
    protected string $table = 'product_attributes';

    protected array $fillable = [
        'product_id',
        'attribute_name',
        'attribute_value',
    ];
}
