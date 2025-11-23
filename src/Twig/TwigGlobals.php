<?php
declare(strict_types=1);
namespace WScore\Deca\Twig;

class TwigGlobals implements TwigLoaderInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(Environment $environment): void
    {
        $environment->addGlobal('_app', $this->container->get(App::class));
        $environment->addGlobal('_setting', $this->container->get(Setting::class));
        $environment->addGlobal('_routes', $this->container->get(RoutingInterface::class));
    }
    
}