<?php

namespace App\Routes\Utils;

use App\Routes\Filters\ArgumentFilters;
use App\Routes\Filters\ControllerArgFilterInterface;
use BadMethodCallException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

trait InvokeMethodTrait
{
    abstract protected function addArgFilter(ControllerArgFilterInterface $filter);

    abstract protected function getContainer(): ContainerInterface;

    /**
     * @param string $method
     * @param array $inputs
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function _invokeMethod(string $method, array $inputs): ResponseInterface
    {
        if (!method_exists($this, $method)) {
            throw new BadMethodCallException("method not found: $method");
        }
        $inputs = $this->filterArgs($method, $inputs);

        $refMethod = new ReflectionMethod($this, $method);
        $parameters = $refMethod->getParameters();
        $arguments = [];
        foreach ($parameters as $arg) {
            $position = $arg->getPosition();
            $varName = $arg->getName();
            if (isset($inputs[$varName])) {
                $arguments[$position] = $inputs[$varName];
                continue;
            }
            if ($arg->isOptional()) {
                $arguments[$position] = $arg->getDefaultValue();
                continue;
            }
            throw new InvalidArgumentException("Argument not found, '$varName', in " . __CLASS__ . '::'.$method);
        }
        $refMethod->setAccessible(true);
        return $refMethod->invokeArgs($this, $arguments);
    }

    /**
     * @param string $method
     * @param array $args
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function filterArgs(string $method, array $args): array
    {
        $refThis = new ReflectionClass($this);
        $this->setFilters($refThis->getAttributes());
        $refMethod = new ReflectionMethod($this, $method);
        $this->setFilters($refMethod->getAttributes());

        $request = $this->getRequest();
        foreach ($this->argFilters as $filter) {
            $args = $filter($request, $args);
        }

        return $args;
    }

    /**
     * @param ReflectionAttribute[] $filters
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            if (is_subclass_of($filter->getName(), ControllerArgFilterInterface::class)) {
                /** @var ControllerArgFilterInterface $object */
                $object = empty($filter->getArguments())
                    ? $this->getContainer()->get($filter->getName())
                    : $filter->newInstance();
                $this->addArgFilter($object);
            }
        }
    }

}