<?php

declare(strict_types=1);

namespace App\Validators;

class UserValidator extends Validator
{
    public static function registration(array $data): self
    {
        return new self($data, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'phone' => 'phone',
        ]);
    }

    public static function login(array $data): self
    {
        return new self($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    }

    public static function passwordReset(array $data): self
    {
        return new self($data, [
            'token' => 'required',
            'password' => 'required|min:8',
        ]);
    }

    public static function profileUpdate(array $data): self
    {
        return new self($data, [
            'first_name' => 'max:100',
            'last_name' => 'max:100',
            'phone' => 'phone',
        ]);
    }
}
