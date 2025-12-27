<?php

namespace Tests\Routes\Utils;

use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\InvokeMethodTrait;

class Invoked
{
    use InvokeMethodTrait;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getName($name): ResponseInterface
    {
        $this->response->getBody()->write("name: $name");
        return $this->response;
    }

    public function getOptional($option = 'option'): ResponseInterface
    {
        $this->response->getBody()->write("option: $option");
        return $this->response;
    }

    public function invoke(string $method, array $args)
    {
        return $this->_invokeMethod($method, $args);
    }
}