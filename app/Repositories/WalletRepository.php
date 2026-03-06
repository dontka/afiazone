<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Wallet());
    }

    public function findByUserId(int $userId): ?Wallet
    {
        /** @var ?Wallet */
        return $this->findBy('user_id', (string) $userId);
    }

    public function getBalance(int $userId): float
    {
        $wallet = $this->findByUserId($userId);
        return $wallet ? (float) $wallet->balance : 0;
    }

    public function getAvailableBalance(int $userId): float
    {
        $wallet = $this->findByUserId($userId);
        return $wallet ? (float) $wallet->available_balance : 0;
    }

    public function getOrCreate(int $userId): Wallet
    {
        return Wallet::getOrCreate($userId);
    }
}
