<?php

declare(strict_types=1);

namespace App\Models;

class CartItem extends BaseModel
{
    protected string $table = 'shopping_cart_items';

    protected array $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_at_add',
    ];

    public function getProduct(): ?Product
    {
        /** @var ?Product */
        return $this->belongsTo(Product::class, 'product_id');
    }
}
