<?php

namespace WScore\Deca\Validation;

use WScore\Deca\Contracts\ValidatorResultInterface;

class ValidatorFailed implements ValidatorResultInterface
{
    public function __construct(private array $errors)
    {
    }

    public function failed(): bool
    {
        return true;
    }

    public function success(): bool
    {
        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValidData(): array
    {
        throw new \RuntimeException('no valid data');
    }
}