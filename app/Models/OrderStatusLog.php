<?php

declare(strict_types=1);

namespace App\Models;

class OrderStatusLog extends BaseModel
{
    protected string $table = 'order_status_logs';

    protected array $fillable = [
        'order_id',
        'previous_status',
        'new_status',
        'changed_by',
        'notes',
    ];
}
