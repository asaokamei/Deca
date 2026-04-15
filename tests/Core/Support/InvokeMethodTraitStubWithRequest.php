<?php

namespace Tests\Core\Support;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WScore\Deca\Controllers\InvokeMethodTrait;

class InvokeMethodTraitStubWithRequest
{
    use InvokeMethodTrait;

    private ResponseInterface $response;
    private ServerRequestInterface $request;

    public function __construct(
        ResponseInterface $response,
        ServerRequestInterface $request
    ) {
        $this->response = $response;
        $this->request = $request;
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

    public function getNoParams(): ResponseInterface
    {
        $this->response->getBody()->write("no params");
        return $this->response;
    }

    public function getArray(array $items = []): ResponseInterface
    {
        $itemsStr = implode(', ', $items);
        $this->response->getBody()->write("array: $itemsStr");
        return $this->response;
    }

    public function getMultiple(string $first = '', string $second = ''): ResponseInterface
    {
        $this->response->getBody()->write("first: $first, second: $second");
        return $this->response;
    }

    public function getMixed(string $name = '', int $age = 0, bool $active = false): ResponseInterface
    {
        $this->response->getBody()->write("name: $name, age: $age, active: " . ($active ? 'true' : 'false'));
        return $this->response;
    }

    public function getAttribute(string $attribute = ''): ResponseInterface
    {
        $this->response->getBody()->write("attribute: $attribute");
        return $this->response;
    }

    public function getArrayOptional(array $items = [], string $name = 'test'): ResponseInterface
    {
        $itemsStr = implode(', ', $items);
        $this->response->getBody()->write("array: $itemsStr, name: $name");
        return $this->response;
    }

    public function invoke(string $method, array $args): ResponseInterface
    {
        return $this->_invokeMethod($method, $args);
    }
}