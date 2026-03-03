<?php

namespace WScore\Deca\Validation;

use WScore\Deca\Contracts\MessageBagInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Views\FormData;

class ValidatorSuccess implements ValidatorResultInterface
{
    public function __construct(private array $rawData, private array $data)
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

    public function getErrorBag(): MessageBagInterface
    {
        throw new \RuntimeException('no errors');
    }

    public function getValidatedData(): array
    {
        return $this->data;
    }

    public function getRawDataBag(): MessageBagInterface
    {
        return new FormData($this->rawData);
    }
}