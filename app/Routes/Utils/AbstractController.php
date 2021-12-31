<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\RoutingInterface;
use App\Application\Interfaces\SessionInterface;
use App\Routes\Filters\ArgumentFilters;
use App\Routes\Filters\ControllerArgFilterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class AbstractController
{
    use InvokeMethodTrait;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var ControllerArgFilterInterface[]
     */
    private $argFilters = [];

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws HttpMethodNotAllowedException
     * @throws ReflectionException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);

        if (method_exists($this, 'action')) {
            return $this->_invokeMethod('action', $this->args);
        }
        $method = 'on' . $this->determineMethod();
        if (method_exists($this, $method)) {
            return $this->_invokeMethod($method, $this->args);
        }
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

    protected function addArgFilter(ControllerArgFilterInterface $filter)
    {
        $this->argFilters[] = $filter;
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
        return $this->getContainer()->get(SessionInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getMessages(): MessageInterface
    {
        return $this->getContainer()->get(MessageInterface::class);
    }

    protected function redirect(): Redirect
    {
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

    protected function regenerateCsRfToken()
    {
        $this->getSession()->regenerateCsRfToken();
    }
}
