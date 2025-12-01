<?php
declare(strict_types=1);

namespace WScore\Deca\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class AbstractControllerInvoker extends AbstractController
{
    use InvokeMethodTrait;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
        $this->args = $args;

        if (method_exists($this, 'action')) {
            return $this->_invokeMethod('action', $this->args);
        }
        $method = 'on' . $this->determineMethod();
        if (method_exists($this, $method)) {
            return $this->_invokeMethod($method, $args);
        }
        throw new HttpMethodNotAllowedException($request);
    }
}
