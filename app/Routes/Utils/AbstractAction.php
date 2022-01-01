<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Application\Interfaces\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

abstract class AbstractAction
{
    use InvokeMethodTrait;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    private array $args = [];

    private ContainerInterface $container;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
        $this->args = $args;

        return $this->_invokeMethod('action', $this->args);
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getSession(): SessionInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getContainer()->get(SessionInterface::class);
    }
}
