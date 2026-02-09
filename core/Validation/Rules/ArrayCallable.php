<?php

namespace WScore\Deca\Validation\Rules;

class ArrayCallable
{
    public function __invoke($subject, $field, $callable): bool
    {
        if (!is_callable($callable)) {
            return false;
        }
        $value = $subject->$field;
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!call_user_func($callable, $item)) {
                return false;
            }
        }
        return true;
    }
}