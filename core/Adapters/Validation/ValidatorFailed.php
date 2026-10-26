<?php

namespace WScore\Deca\Adapters\Validation;

use WScore\Deca\Contracts\MessageBagInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Adapters\View\FormData;
use WScore\Deca\Adapters\View\FormDotted;

class ValidatorFailed implements ValidatorResultInterface
{
    public function __construct(private array $rawData, private array $errors)
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

    public function getErrorBag(): MessageBagInterface
    {
        return new FormDotted($this->errors);
    }

    public function getValidatedData(): array
    {
        throw new \RuntimeException('no valid data');
    }

    public function getRawDataBag(): MessageBagInterface
    {
        return new FormData($this->rawData);
    }
}