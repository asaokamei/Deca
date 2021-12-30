<?php
declare(strict_types=1);

namespace App\Routes\Utils;

use App\Routes\Filters\ControllerArgFilterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

abstract class AbstractAction
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
     * @throws ReflectionException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $request->getAttribute(ContainerInterface::class);
        $this->args = $this->filterArgs($args);

        return $this->_invokeMethod('action', $this->args);
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

    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
