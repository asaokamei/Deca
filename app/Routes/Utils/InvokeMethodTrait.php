<?php

namespace App\Routes\Utils;

use BadMethodCallException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;

trait InvokeMethodTrait
{
    protected function _invokeMethod(string $method, array $inputs): ResponseInterface
    {
        if (!method_exists($this, $method)) {
            throw new BadMethodCallException("method, '$method', not found in " . __CLASS__);
        }

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
                /** @noinspection PhpUnhandledExceptionInspection */
                $arguments[$position] = $arg->getDefaultValue();
                continue;
            }
            throw new InvalidArgumentException("Argument, '$varName', not found in " . __CLASS__ . '::'.$method);
        }
        $refMethod->setAccessible(true);
        /** @noinspection PhpUnhandledExceptionInspection */
        return $refMethod->invokeArgs($this, $arguments);
    }
}