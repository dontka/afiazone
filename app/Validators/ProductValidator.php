<?php

declare(strict_types=1);

namespace App\Validators;

class ProductValidator extends Validator
{
    public static function create(array $data): self
    {
        return new self($data, [
            'name' => 'required|max:255',
            'sku' => 'required|max:100',
            'price' => 'required|numeric|positive',
            'category_id' => 'required|numeric',
            'description' => 'max:5000',
            'prescription_required' => 'boolean',
        ]);
    }

    public static function update(array $data): self
    {
        return new self($data, [
            'name' => 'max:255',
            'price' => 'numeric|positive',
            'category_id' => 'numeric',
            'description' => 'max:5000',
            'prescription_required' => 'boolean',
        ]);
    }
}
