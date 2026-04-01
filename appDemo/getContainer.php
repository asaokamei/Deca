<?php
declare(strict_types=1);


use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Definitions;
use WScore\Deca\Contracts\MailerInterface;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Services\PhpMailer;
use WScore\Deca\Services\Routing;
use WScore\Deca\Services\Session;
use WScore\Deca\Services\SessionAura;
use WScore\Deca\Services\Setting;
use WScore\Deca\Views\Twig\ViewTwig;

require_once dirname(__DIR__) . '/vendor/autoload.php';

function getContainer(?Setting $setting = null): ContainerInterface
{
    $definitions = new Definitions();
    if (isset($setting)) {
        $definitions->setValue(Setting::class, $setting);
    }
    $definitions->setValue(Definitions::APP_DIR, __DIR__);
    $definitions->setValue(Definitions::VAR_DIR, dirname(__DIR__) . '/var');
    $definitions->setAlias(RoutingInterface::class, Routing::class);
    $definitions->setAlias(SessionInterface::class, Session::class);
    $definitions->setAlias(MessageInterface::class, Messages::class);
    $definitions->setAlias(ViewInterface::class, ViewTwig::class);
    $definitions->setAlias(MailerInterface::class, PhpMailer::class);

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions($definitions->getDefinitions());
    $container = $containerBuilder->build();

    return $container;
}
