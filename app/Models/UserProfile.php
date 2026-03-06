<?php

declare(strict_types=1);

namespace App\Models;

class UserProfile extends BaseModel
{
    protected string $table = 'user_profiles';
    protected string $primaryKey = 'user_id';

    protected array $fillable = [
        'user_id',
        'bio',
        'avatar_url',
        'country',
        'city',
        'address',
        'postal_code',
        'company_name',
        'company_type',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }
}
