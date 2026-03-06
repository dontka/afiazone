<?php

declare(strict_types=1);

namespace App\Models;

class MerchantStock extends BaseModel
{
    protected string $table = 'merchant_stocks';

    protected array $fillable = [
        'merchant_id',
        'product_id',
        'variant_id',
        'quantity',
        'reorder_level',
        'last_restock_date',
    ];

    public function getProduct(): ?Product
    {
        /** @var ?Product */
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getMerchant(): ?Merchant
    {
        /** @var ?Merchant */
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}
