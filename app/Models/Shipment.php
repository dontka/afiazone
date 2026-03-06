<?php

declare(strict_types=1);

namespace App\Models;

class Shipment extends BaseModel
{
    protected string $table = 'shipments';

    protected array $fillable = [
        'order_id',
        'tracking_number',
        'delivery_personnel_id',
        'provider_id',
        'status',
        'estimated_delivery_date',
        'actual_delivery_date',
        'delivery_code',
        'qr_code_url',
        'pickup_location',
        'signature_required',
        'delivery_notes',
    ];

    public function getOrder(): ?Order
    {
        /** @var ?Order */
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getTrackingLogs(): array
    {
        return $this->hasMany(ShipmentTrackingLog::class, 'shipment_id');
    }

    public static function findByTrackingNumber(string $tracking): ?self
    {
        return self::findBy('tracking_number', $tracking);
    }

    public static function generateTrackingNumber(): string
    {
        return 'AZ-SHP-' . strtoupper(bin2hex(random_bytes(6)));
    }

    public static function generateDeliveryCode(): string
    {
        return str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    }
}
