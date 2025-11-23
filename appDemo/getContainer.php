<?php
declare(strict_types=1);


use DI\ContainerBuilder;
use WScore\Deca\Definitions;

require __DIR__ . '/../vendor/autoload.php';

$definitions = new Definitions();
$definitions->set(Definitions::APP_DIR, __DIR__);

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions($definitions->getDefinitions());
$container = $containerBuilder->build();

return $container;