<?php

declare(strict_types=1);

namespace App\Models;

class WalletReservation extends BaseModel
{
    protected string $table = 'wallet_reservations';

    protected array $fillable = [
        'wallet_id',
        'order_id',
        'amount',
        'reason',
        'status',
        'released_at',
    ];
}
