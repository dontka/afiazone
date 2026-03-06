<?php

declare(strict_types=1);

namespace App\Models;

class MedicalRecord extends BaseModel
{
    protected string $table = 'medical_records';

    protected array $fillable = [
        'user_id',
        'record_type',
        'title',
        'description',
        'provider_name',
        'provider_facility',
        'recorded_date',
        'file_url',
        'is_shared_with_all_providers',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }
}
