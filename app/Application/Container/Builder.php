<?php

namespace App\Application\Container;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;

class Builder
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;
    /**
     * @var Container|ContainerInterface
     */
    private $container;

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
        $this->containerBuilder->addDefinitions($definitions);
    }

    /**
     * @return ContainerInterface
     * @throws Exception
     */
    public function build(): ContainerInterface
    {
        $this->container = $this->containerBuilder->build();
        return $this->container;
    }

    public function set(string $name, $value)
    {
        $this->container->set($name, $value);
    }
}