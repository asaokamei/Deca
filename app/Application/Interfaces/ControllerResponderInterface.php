<?php

namespace App\Application\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerResponderInterface
{
    public function set(ServerRequestInterface $request, ResponseInterface $response);
}