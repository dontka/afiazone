<?php

declare(strict_types=1);

namespace App\Models;

class ShipmentTrackingLog extends BaseModel
{
    protected string $table = 'shipment_tracking_logs';

    protected array $fillable = [
        'shipment_id',
        'status',
        'location',
        'notes',
        'gps_lat',
        'gps_lon',
    ];
}
