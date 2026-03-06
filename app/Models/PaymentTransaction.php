<?php

declare(strict_types=1);

namespace App\Models;

class PaymentTransaction extends BaseModel
{
    protected string $table = 'payment_transactions';

    protected array $fillable = [
        'order_id',
        'amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'status',
        'payment_date',
        'completion_date',
        'failure_reason',
        'metadata',
    ];

    public function getOrder(): ?Order
    {
        /** @var ?Order */
        return $this->belongsTo(Order::class, 'order_id');
    }
}
