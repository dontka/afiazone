<?php

declare(strict_types=1);

namespace App\Models;

class ProductReview extends BaseModel
{
    protected string $table = 'product_reviews';

    protected array $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'status',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }
}
