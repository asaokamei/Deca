<?php

namespace App\Application\Container;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;

class Builder
{
    private ContainerBuilder $containerBuilder;

    private Container|ContainerInterface $container;

    private array $definitions = [];

    public function __construct()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    public function enableCompilation(string $cacheDir)
    {
        $this->containerBuilder->enableCompilation($cacheDir);
    }

    public function addDefinitions(array $definitions)
    {
        $this->definitions = array_merge($this->definitions, $definitions);
    }

    public function build(): ContainerInterface
    {
        $this->containerBuilder->addDefinitions($this->definitions);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->container = $this->containerBuilder->build();
        return $this->container;
    }

    public function set(string $name, $value)
    {
        $this->container->set($name, $value);
    }
}