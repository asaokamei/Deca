<?php
declare(strict_types=1);

namespace App\Application\Container;


use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;

class BootContainer
{
    /**
     * @var bool
     */
    private $useCache = false;

    /**
     * @var null|string
     */
    private $cacheDir = null;

    /**
     * @var Setting
     */
    private $settings = [];

    /**
     * @var Provider
     */
    private $provider;

    public static function forge(Setting $settings, string $cacheDir): self
    {
        $self = new self();
        $self->settings = $settings;
        $self->cacheDir = $cacheDir;
        $self->provider = new Provider();

        return $self;
    }

    /**
     * @return ContainerInterface|Container
     * @throws Exception
     */
    public function build(): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();

        if ($this->useCache && $this->cacheDir) { // compilation not working, yet
            if ($containerBuilder->isCompilationEnabled()) {
                $containerBuilder->enableCompilation($this->cacheDir);
            }
        }

        $containerBuilder->addDefinitions([
            'settings' => $this->settings,
            Setting::class => $this->settings,
        ]);

        // Set up dependencies
        $this->populate($containerBuilder);

        // Build PHP-DI Container instance
        return $containerBuilder->build();
    }

    private function populate(ContainerBuilder $containerBuilder)
    {
        $dependencies = $this->provider->getDefinitions();
        $containerBuilder->addDefinitions($dependencies);
    }

    /**
     * @param bool $useCache
     * @return BootContainer
     */
    public function setUseCache(bool $useCache): BootContainer
    {
        $this->useCache = $useCache;
        return $this;
    }
}