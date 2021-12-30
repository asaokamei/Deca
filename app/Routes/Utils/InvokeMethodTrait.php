<?php

namespace App\Routes\Utils;

use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use ReflectionMethod;

trait InvokeMethodTrait
{
    /**
     * @param string $method
     * @param array $inputs
     * @return ResponseInterface
     * @throws ReflectionException
     */
    protected function _invokeMethod(string $method, array $inputs): ResponseInterface
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("method not found: $method");
        }
        $method = new ReflectionMethod($this, $method);
        $parameters = $method->getParameters();
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
            throw new InvalidArgumentException("argument not found for: $varName");
        }
        $method->setAccessible(true);
        return $method->invokeArgs($this, $arguments);
    }

}