<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Application\Interfaces\ControllerArgFilterInterface;
use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use App\Application\Middleware\AppMiddleware;
use App\Controllers\Filters\Redirect;
use App\Controllers\Filters\Respond;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionMethod;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class AbstractController
{
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
     * @var App
     */
    private $app;

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
        $this->app = $request->getAttribute(AppMiddleware::APP_NAME);
        $this->container = $this->app->getContainer();
        $this->args = $this->filterArgs($args);

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

    /**
     * @param string $method
     * @param array $inputs
     * @return ResponseInterface
     * @throws ReflectionException
     */
    protected function _invokeMethod(string $method, array $inputs): ResponseInterface
    {
        $method = new ReflectionMethod($this, $method);
        $parameters = $method->getParameters();
        $arguments = [];
        foreach ($parameters as $arg) {
            $position = $arg->getPosition();
            $varName = $arg->getName();
            $optionValue = $arg->isOptional() ? $arg->getDefaultValue() : null;
            $value = $inputs[$varName] ?? $optionValue;
            $arguments[$position] = $value;
        }
        $method->setAccessible(true);
        return $method->invokeArgs($this, $arguments);
    }

    protected function filterArgs(array $args): array
    {
        $request = $this->getRequest();
        foreach ($this->argFilters as $filter) {
            $args = $filter($request, $args);
        }

        return $args;
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
        return new Redirect($this->app, $this->response);
    }

    protected function respond(): Respond
    {
        return new Respond($this->app, $this->container, $this->response);
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
