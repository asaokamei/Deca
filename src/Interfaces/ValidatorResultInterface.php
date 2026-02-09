<?php

namespace WScore\Deca\Interfaces;

interface ValidatorResultInterface
{
    public function failed(): bool;

    public function success(): bool;

    public function getErrors(): array;

    public function getValidData(): array;
}