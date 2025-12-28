<?php

namespace WScore\Deca\Validation\Rules;

class ArrayValues
{
    public function __invoke($subject, $field, $list): bool
    {
        $value = $subject->$field;
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!in_array($item, $list)) {
                return false;
            }
        }
        return true;
    }
}