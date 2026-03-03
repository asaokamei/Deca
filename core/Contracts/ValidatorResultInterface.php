<?php

namespace WScore\Deca\Contracts;

interface ValidatorResultInterface
{
    public function failed(): bool;

    public function success(): bool;

    public function getErrorBag(): MessageBagInterface;

    public function getValidatedData(): array;

    public function getRawDataBag(): MessageBagInterface;
}