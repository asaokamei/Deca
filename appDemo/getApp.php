<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use WScore\Deca\Handlers\SimpleErrorHandler;
use WScore\Deca\Middleware\AppMiddleware;
use WScore\Deca\Middleware\CsRfGuard;
use WScore\Deca\Services\Setting;

function getApp(ContainerInterface $container): App
{
    $app = AppFactory::createFromContainer($container);

    // set up middlewares
    $app->addRoutingMiddleware();
    $app->add(CsRfGuard::class);
    $app->add(AppMiddleware::class);

    $settings = $container->get(Setting::class);
    $displayErrorDetails = (bool) ($settings['display_errors'] ?? false);
    $errorMiddleware = $app->addErrorMiddleware(
        $displayErrorDetails,
        true,
        true,
        $container->get(LoggerInterface::class)
    );
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('text/html', SimpleErrorHandler::class);

    // register $app self and routeCollector.
    if ($container instanceof Container) {
        $container->set(App::class, $app);
        $container->set(RouteCollectorInterface::class, $app->getRouteCollector());
    } else {
        throw new \LogicException('container must be DI\Container.');
    }

    return $app;
}

