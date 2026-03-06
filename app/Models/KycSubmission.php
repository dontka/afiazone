<?php

declare(strict_types=1);

namespace App\Models;

class KycSubmission extends BaseModel
{
    protected string $table = 'kyc_submissions';

    protected array $fillable = [
        'user_id',
        'status',
        'submission_date',
        'review_date',
        'reviewer_id',
        'rejection_reason',
        'internal_notes',
    ];

    public function getDocuments(): array
    {
        return $this->hasMany(KycDocument::class, 'kyc_submission_id');
    }

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function findByUserId(int $userId): ?self
    {
        return self::findBy('user_id', (string) $userId);
    }
}
