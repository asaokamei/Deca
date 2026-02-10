<?php

namespace WScore\Deca\Contracts;

interface ValidatorInterface
{
    public function validate(array $data): ValidatorResultInterface;
}