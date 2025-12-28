<?php

namespace WScore\Deca\Interfaces;

interface ValidatorInterface
{
    public function validate(array $data): bool;

    public function failed(): bool;

    public function success(): bool;

    public function getErrors(): array;

    public function getValidData(): array;
}