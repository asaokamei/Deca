<?php
declare(strict_types=1);

namespace WScore\Deca\Controllers;

use WScore\Deca\Interfaces\MessageInterface;
use WScore\Deca\Interfaces\RoutingInterface;
use WScore\Deca\Interfaces\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class AbstractController
{
    use InvokeMethodTrait;

    protected ServerRequestInterface $request;

    protected ResponseInterface $response;

    protected array $args = [];

    protected ContainerInterface $container;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
        $this->args = $args;

        if (method_exists($this, 'action')) {
            return $this->_invokeMethod('action', $this->args);
        }
        $method = 'on' . 
            $this->request->getParsedBody()['_method'] 
            ?? $this->request->getMethod();
        if (method_exists($this, $method)) {
            return $this->$method($this->request, $this->args);
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new HttpMethodNotAllowedException($request);
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    protected function request(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function session(): SessionInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->container->get(SessionInterface::class);
    }

    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    protected function messages(): MessageInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->container->get(MessageInterface::class);
    }

    protected function redirect(): Redirect
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Redirect($this->container->get(RoutingInterface::class), $this->response);
    }

    protected function respond(): Respond
    {
        $respond = new Respond($this->container, $this->response);
        $respond->setRequest($this->request);
        return $respond;
    }

    /**
     * a quick way to render a view (skip getting the respond object)
     */
    protected function view(string $template, array $data = []): ResponseInterface
    {
        $respond = $this->respond();
        return $respond->view($template, $data);
    }
}
