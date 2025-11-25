<?php
declare(strict_types=1);


use DI\ContainerBuilder;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Definitions;
use WScore\Deca\Interfaces\MessageInterface;
use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Interfaces\ViewInterface;
use WScore\Deca\Services\SessionAura;
use WScore\Deca\Services\Setting;
use WScore\Deca\Services\ViewTwig;

require __DIR__ . '/../vendor/autoload.php';

$definitions = new Definitions();
if (isset($setting) && $setting instanceof Setting) {
    $definitions->setValue(Setting::class, $setting);
}
$definitions->setValue(Definitions::APP_DIR, __DIR__);
$definitions->setAlias(SessionInterface::class, SessionAura::class);
$definitions->setAlias(MessageInterface::class, Messages::class);
$definitions->setAlias(ViewInterface::class, ViewTwig::class);

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions($definitions->getDefinitions());
$container = $containerBuilder->build();

return $container;