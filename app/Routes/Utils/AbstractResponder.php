<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Application\Interfaces\ControllerResponderInterface;
use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\RoutingInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractResponder implements ControllerResponderInterface
{
    use InvokeMethodTrait;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    private ContainerInterface $container;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getMessages(): MessageInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getContainer()->get(MessageInterface::class);
    }

    protected function redirect(): Redirect
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Redirect($this->container->get(RoutingInterface::class), $this->response);
    }

    protected function respond(): Respond
    {
        return new Respond($this->container, $this->response);
    }
}
