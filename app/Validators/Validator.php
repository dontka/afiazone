<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

abstract class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $errors = [];

    public function __construct(array $data, array $rules = [])
    {
        $this->data = $data;
        $this->rules = $rules ?: $this->rules;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rulesList = explode('|', $rules);
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $value, ...$params)) {
                        break; // stop at first error per field
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate and throw on failure.
     */
    public function validateOrFail(): void
    {
        if (!$this->validate()) {
            throw new ValidationException('Validation failed', $this->errors);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // ── Built-in rules ──────────────────────────

    protected function validateRequired(string $field, mixed $value): bool
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[$field] = "{$field} is required";
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, mixed $value): bool
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$field} must be a valid email";
            return false;
        }
        return true;
    }

    protected function validateNumeric(string $field, mixed $value): bool
    {
        if ($value !== null && !is_numeric($value)) {
            $this->errors[$field] = "{$field} must be numeric";
            return false;
        }
        return true;
    }

    protected function validateMin(string $field, mixed $value, string $length = '0'): bool
    {
        if ($value && strlen((string) $value) < (int) $length) {
            $this->errors[$field] = "{$field} must be at least {$length} characters";
            return false;
        }
        return true;
    }

    protected function validateMax(string $field, mixed $value, string $length = '255'): bool
    {
        if ($value && strlen((string) $value) > (int) $length) {
            $this->errors[$field] = "{$field} must not exceed {$length} characters";
            return false;
        }
        return true;
    }

    protected function validateIn(string $field, mixed $value, string ...$options): bool
    {
        if ($value && !in_array((string) $value, $options, true)) {
            $this->errors[$field] = "{$field} must be one of: " . implode(', ', $options);
            return false;
        }
        return true;
    }

    protected function validatePhone(string $field, mixed $value): bool
    {
        if ($value && !preg_match('/^\+?[0-9]{8,15}$/', (string) $value)) {
            $this->errors[$field] = "{$field} must be a valid phone number";
            return false;
        }
        return true;
    }

    protected function validateUrl(string $field, mixed $value): bool
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "{$field} must be a valid URL";
            return false;
        }
        return true;
    }

    protected function validateBoolean(string $field, mixed $value): bool
    {
        if ($value !== null && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->errors[$field] = "{$field} must be boolean";
            return false;
        }
        return true;
    }

    protected function validatePositive(string $field, mixed $value): bool
    {
        if ($value !== null && (!is_numeric($value) || (float) $value <= 0)) {
            $this->errors[$field] = "{$field} must be positive";
            return false;
        }
        return true;
    }
}
