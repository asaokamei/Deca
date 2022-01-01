<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Application\Interfaces\ControllerArgFilterInterface;
use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\RoutingInterface;
use App\Application\Interfaces\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class AbstractController
{
    use InvokeMethodTrait;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    private array $args = [];

    /**
     * @var ControllerArgFilterInterface[]
     */
    private array $argFilters = [];

    private ContainerInterface $container;

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
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new HttpMethodNotAllowedException($request);
    }

    /**
     * Override this method to change which method to invoke.
     * Default is to use $_POST['_method'], or http method.
     *
     * @return string
     */
    protected function determineMethod(): string
    {
        return $this->request->getParsedBody()['_method'] ?? $this->request->getMethod();
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function getSession(): SessionInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getContainer()->get(SessionInterface::class);
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

    protected function view(string $template, array $data = []): ResponseInterface
    {
        return $this->respond()->view($template, $data);
    }
}
