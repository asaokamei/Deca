<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Application\Interfaces\ControllerArgFilterInterface;
use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use App\Application\Middleware\AppMiddleware;
use App\Application\Middleware\SessionMiddleware;
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
     * @var SessionInterface
     */
    private $session;

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
        $this->session = $request->getAttribute(SessionMiddleware::SESSION_NAME);
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
        $request = $this->request();
        foreach ($this->argFilters as $filter) {
            $args = $filter($request, $args);
        }

        return $args;
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     */
    protected function view(string $template, array $data = []): ResponseInterface
    {
        $this->session->clearFlash(); // rendering a view means ...
        $view = $this->app->getContainer()->get(ViewInterface::class);
        return $view->render($this->response, $template, $data);
    }

    protected function request(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function session(): SessionInterface
    {
        return $this->session;
    }

    protected function container(): ContainerInterface
    {
        return $this->app->getContainer();
    }

    protected function flashMessage($message)
    {
        $messages = (array) $this->session->getFlash('messages', []);
        $messages[] = $message;
        $this->session->setFlash('messages', $messages);
    }

    protected function flashNotice($message)
    {
        $messages = (array) $this->session->getFlash('notices', []);
        $messages[] = $message;
        $this->session->setFlash('notices', $messages);
    }

    protected function redirectToRoute(string $string, $options = [], $query = []): ResponseInterface
    {
        $url = $this->urlFor($string, $options, $query);

        return $this->response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    protected function urlFor(string $string, $options = [], $query = []): string
    {
        $routeParser = $this->app->getRouteCollector()->getRouteParser();
        return $routeParser->urlFor($string, $options, $query);
    }

    protected function regenerateCsRfToken()
    {
        $this->session->regenerateCsRfToken();
    }
}
