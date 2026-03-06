<?php

declare(strict_types=1);

namespace App\Models;

class Merchant extends BaseModel
{
    protected string $table = 'merchants';

    protected array $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'tier_id',
        'description',
        'logo_url',
        'cover_image_url',
        'rating',
        'total_reviews',
        'total_sales',
        'status',
        'verification_date',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getProducts(): array
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }

    public function getStocks(): array
    {
        return $this->hasMany(MerchantStock::class, 'merchant_id');
    }

    public static function findByUserId(int $userId): ?self
    {
        return self::findBy('user_id', (string) $userId);
    }
}
