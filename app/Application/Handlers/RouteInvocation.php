<?php

namespace App\Application\Handlers;

use App\Routes\Filters\ControllerArgFilterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionException;
use Slim\Interfaces\InvocationStrategyInterface;

class RouteInvocation implements InvocationStrategyInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        $routeArguments = $this->filterArguments($callable, $request, $routeArguments);
        return $callable($request, $response, $routeArguments);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function filterArguments(callable $callable, ServerRequestInterface $request, array $routeArguments): array
    {
        $object = is_array($callable)
            ? $callable[0]
            : $callable;
        $refThis = new ReflectionClass($object);
        foreach ($refThis->getAttributes() as $filter) {
            if (!is_subclass_of($filter->getName(), ControllerArgFilterInterface::class)) {
                continue;
            }
            /** @var ControllerArgFilterInterface $object */
            $object = empty($filter->getArguments())
                ? $this->container->get($filter->getName())
                : $filter->newInstance();
            $routeArguments = $object->__invoke($request, $routeArguments);
        }
        return $routeArguments;
    }
}