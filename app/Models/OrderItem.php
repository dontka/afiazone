<?php

declare(strict_types=1);

namespace App\Models;

class OrderItem extends BaseModel
{
    protected string $table = 'order_items';

    protected array $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_amount',
        'subtotal',
    ];

    public function getProduct(): ?Product
    {
        /** @var ?Product */
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getOrder(): ?Order
    {
        /** @var ?Order */
        return $this->belongsTo(Order::class, 'order_id');
    }
}
