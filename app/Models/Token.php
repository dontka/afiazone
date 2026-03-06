<?php

declare(strict_types=1);

namespace App\Models;

class Token extends BaseModel
{
    protected string $table = 'tokens';

    protected array $fillable = [
        'user_id',
        'token_type',
        'token_hash',
        'expires_at',
        'is_used',
        'ip_address',
        'user_agent',
    ];

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return strtotime($this->expires_at) < time();
    }

    public function markUsed(): bool
    {
        return $this->update(['is_used' => true]);
    }

    public static function findByHash(string $hash): ?self
    {
        return self::query()
            ->where('token_hash', $hash)
            ->where('is_used', false)
            ->first();
    }

    public static function createForUser(int $userId, string $type, int $expiresInSeconds = 3600): self
    {
        $tokenValue = bin2hex(random_bytes(32));
        return self::create([
            'user_id' => $userId,
            'token_type' => $type,
            'token_hash' => hash('sha256', $tokenValue),
            'expires_at' => date('Y-m-d H:i:s', time() + $expiresInSeconds),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
