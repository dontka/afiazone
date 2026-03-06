<?php

declare(strict_types=1);

namespace App\Validators;

class WalletValidator extends Validator
{
    public static function topUp(array $data): self
    {
        return new self($data, [
            'amount' => 'required|numeric|positive',
            'payment_method' => 'required|in:card,mobile_money',
        ]);
    }

    public static function transfer(array $data): self
    {
        return new self($data, [
            'amount' => 'required|numeric|positive',
            'recipient_id' => 'required|numeric',
        ]);
    }
}
