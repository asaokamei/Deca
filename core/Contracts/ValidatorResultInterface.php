<?php

namespace WScore\Deca\Contracts;

interface ValidatorResultInterface
{
    public function failed(): bool;

    public function success(): bool;

    public function getErrors(): array;

    public function getValidatedData(): array;

    public function getRawData(): array;
}