<?php

namespace App\Routes\Filters;

use Psr\Http\Message\ServerRequestInterface;

interface ControllerArgFilterInterface
{
    public function __invoke(ServerRequestInterface $request, array $args): array;
}