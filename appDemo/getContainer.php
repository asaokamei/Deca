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
use WScore\Deca\Views\Twig\ViewTwig;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('getContainer')) {
    /**
     * Build the DI container.
     *
     * @param string|null $settingsIniPath Absolute path to settings.ini. Defaults to project root settings.ini (next to appDemo/).
     * @param Definitions|null $definitions Optional Definitions instance to extend (e.g. setValue(Setting::class, ...) for tests).
     */
    function getContainer(?string $settingsIniPath = null, ?Definitions $definitions = null): ContainerInterface
    {
        $definitions = $definitions ?? new Definitions();
        $path = $settingsIniPath ?? dirname(__DIR__) . '/settings.ini';
        $definitions->setValue(Definitions::SETTINGS_INI_PATH, $path);
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

}
