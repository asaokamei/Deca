<?php

namespace WScore\Deca\Validation;

use WScore\Deca\Contracts\ValidatorResultInterface;

class ValidatorSuccess implements ValidatorResultInterface
{
    public function __construct(private array $data)
    {
    }

    public function failed(): bool
    {
        return false;
    }

    public function success(): bool
    {
        return true;
    }

    public function getErrors(): array
    {
        throw new \RuntimeException('no errors');
    }

    public function getValidData(): array
    {
        return $this->data;
    }
}