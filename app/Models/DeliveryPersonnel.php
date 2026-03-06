<?php

declare(strict_types=1);

namespace App\Models;

class DeliveryPersonnel extends BaseModel
{
    protected string $table = 'delivery_personnel';

    protected array $fillable = [
        'user_id',
        'provider_id',
        'license_type',
        'license_number',
        'license_expiry',
        'vehicle_type',
        'vehicle_license_plate',
        'is_available',
        'current_location_lat',
        'current_location_lon',
        'tier_id',
        'average_rating',
        'total_deliveries',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function findByUserId(int $userId): ?self
    {
        return self::findBy('user_id', (string) $userId);
    }

    public static function findAvailable(): array
    {
        return self::query()->where('is_available', true)->get();
    }
}
