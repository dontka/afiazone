<?php

declare(strict_types=1);

namespace App\Validators;

class OrderValidator extends Validator
{
    public static function create(array $data): self
    {
        return new self($data, [
            'payment_method' => 'required|in:cash_on_delivery,wallet,card,mobile_money',
        ]);
    }

    public static function statusUpdate(array $data): self
    {
        return new self($data, [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
        ]);
    }
}
