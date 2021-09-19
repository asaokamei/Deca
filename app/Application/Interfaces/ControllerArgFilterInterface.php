<?php

namespace App\Application\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface ControllerArgFilterInterface
{
    public function __invoke(ServerRequestInterface $request, array $args): array;
}