<?php

namespace App\Routes\Filters;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class ArgumentFilters
{
    public function __construct($class, ...$args)
    {
        var_dump($class, $args);
    }
}