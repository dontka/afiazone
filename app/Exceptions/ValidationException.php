<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends HttpException
{
    protected array $errors = [];

    public function __construct(string $message = '', array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct($message, 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
