<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $rulesForField = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

            if (in_array('nullable', $rulesForField, true) && ($value === null || $value === '')) {
                continue;
            }

            foreach ($rulesForField as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $argument] = array_pad(explode(':', $rule, 2), 2, null);
        $missing = $value === null || $value === '';

        if ($name === 'required' && $missing) {
            $this->addError($field, 'Ce champ est obligatoire.');
            return;
        }

        if ($missing) {
            return;
        }

        if ($name === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, 'Cette adresse email est invalide.');
        }

        if ($name === 'string' && ! is_string($value)) {
            $this->addError($field, 'Ce champ doit être du texte.');
        }

        if ($name === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'Ce champ doit être un nombre entier.');
        }

        if ($name === 'min' && is_scalar($value) && strlen((string) $value) < (int) $argument) {
            $this->addError($field, 'Ce champ est trop court.');
        }

        if ($name === 'max' && is_scalar($value) && strlen((string) $value) > (int) $argument) {
            $this->addError($field, 'Ce champ est trop long.');
        }

        if ($name === 'in' && ! in_array((string) $value, explode(',', (string) $argument), true)) {
            $this->addError($field, 'La valeur choisie est invalide.');
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}