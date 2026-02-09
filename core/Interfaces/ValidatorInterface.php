<?php

namespace WScore\Deca\Interfaces;

interface ValidatorInterface
{
    public function validate(array $data): ValidatorResultInterface;
}