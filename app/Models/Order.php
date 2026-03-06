<?php

declare(strict_types=1);

namespace App\Models;

class Order extends BaseModel
{
    protected string $table = 'orders';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'tax_amount',
        'shipping_fee',
        'discount_amount',
        'final_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getItems(): array
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function getShipment(): ?BaseModel
    {
        return $this->hasOne(Shipment::class, 'order_id');
    }

    public function getStatusHistory(): array
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id');
    }

    public function getPrescription(): ?BaseModel
    {
        return $this->hasOne(Prescription::class, 'order_id');
    }

    public static function findByOrderNumber(string $orderNumber): ?self
    {
        return self::findBy('order_number', $orderNumber);
    }

    public static function generateOrderNumber(): string
    {
        return 'AZ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function logStatusChange(string $newStatus, ?int $changedBy = null, string $notes = ''): void
    {
        $previousStatus = $this->order_status;
        OrderStatusLog::create([
            'order_id' => $this->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
        ]);
    }
}
