<?php

declare(strict_types=1);

namespace App\Models;

class Wallet extends BaseModel
{
    protected string $table = 'wallets';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'currency',
        'balance',
        'reserved_balance',
        'available_balance',
        'total_received',
        'total_spent',
        'status',
    ];

    public function getAvailableBalance(): float
    {
        return (float) ($this->available_balance ?? 0);
    }

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTransactions(?int $limit = null): array
    {
        $query = WalletTransaction::query()
            ->where('wallet_id', $this->id)
            ->orderBy('created_at', 'DESC');
        if ($limit) {
            $query->limit($limit);
        }
        return $query->get();
    }

    public function getReservations(): array
    {
        return $this->hasMany(WalletReservation::class, 'wallet_id');
    }

    public static function findByUserId(int $userId): ?self
    {
        return self::findBy('user_id', (string) $userId);
    }

    public static function getOrCreate(int $userId): self
    {
        $wallet = self::findByUserId($userId);
        if ($wallet) {
            return $wallet;
        }
        return self::create([
            'user_id' => $userId,
            'currency' => 'USD',
            'balance' => 0,
            'reserved_balance' => 0,
            'available_balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
            'status' => 'active',
        ]);
    }
}
