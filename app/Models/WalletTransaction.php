<?php

declare(strict_types=1);

namespace App\Models;

class WalletTransaction extends BaseModel
{
    protected string $table = 'wallet_transactions';

    protected array $fillable = [
        'wallet_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'external_reference',
        'payment_method',
        'description',
        'metadata',
        'status',
    ];

    public function getWallet(): ?Wallet
    {
        /** @var ?Wallet */
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
