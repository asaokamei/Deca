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
     * @var null|string
     */
    private $cacheDir = null;

    /**
     * @var Setting
     */
    private $settings;

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
    public function build(bool $useCache = false): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();

        if ($useCache && $this->cacheDir) { // compilation not working, yet
            $containerBuilder->enableCompilation($this->cacheDir);
        }

        // Set up dependencies
        $containerBuilder->addDefinitions([
            'settings' => $this->settings,
            Setting::class => $this->settings,
        ]);
        $containerBuilder->addDefinitions($this->provider->getDefinitions());

        // Build PHP-DI Container instance
        return $containerBuilder->build();
    }
}