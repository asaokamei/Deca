<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WScore\Deca\Definitions;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('getContainer')) {
    /**
     * Build the DI container from assembled definitions.
     */
    function getContainer(Definitions $definitions): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($definitions->getDefinitions());

        return $containerBuilder->build();
    }
}
