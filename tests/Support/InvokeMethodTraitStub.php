<?php

namespace Tests\Support;

use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\InvokeMethodTrait;

/**
 * Stub controller for testing InvokeMethodTrait.
 */
class InvokeMethodTraitStub
{
    use InvokeMethodTrait;

    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getName(string $name): ResponseInterface
    {
        $this->response->getBody()->write("name: $name");
        return $this->response;
    }

    public function getOptional(string $option = 'option'): ResponseInterface
    {
        $this->response->getBody()->write("option: $option");
        return $this->response;
    }

    public function invoke(string $method, array $args): ResponseInterface
    {
        return $this->_invokeMethod($method, $args);
    }
}
