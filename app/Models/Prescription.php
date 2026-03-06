<?php

declare(strict_types=1);

namespace App\Models;

class Prescription extends BaseModel
{
    protected string $table = 'prescriptions';

    protected array $fillable = [
        'order_id',
        'user_id',
        'prescriber_name',
        'prescriber_license',
        'prescriber_contact',
        'image_url',
        'prescription_date',
        'expiry_date',
        'verification_status',
        'verified_by',
        'verification_date',
        'rejection_reason',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getOrder(): ?Order
    {
        /** @var ?Order */
        return $this->belongsTo(Order::class, 'order_id');
    }
}
