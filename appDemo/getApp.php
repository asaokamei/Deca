<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use WScore\Deca\Handlers\SimpleErrorHandler;
use WScore\Deca\Middleware\AppMiddleware;
use WScore\Deca\Middleware\CsRfGuard;
use WScore\Deca\Services\Setting;

/** @var ContainerInterface $container */
if (!isset($container)) {
    return null;
}
if (!$container instanceof ContainerInterface) {
    return null;
}
$app = AppFactory::createFromContainer($container);

// set up middlewares
$app->addRoutingMiddleware();
$app->add(CsRfGuard::class);
$app->add(AppMiddleware::class);

$settings = $container->get(Setting::class);
$displayErrorDetails = (bool) ($settings['display_errors'] ?? false);
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer('text/html', SimpleErrorHandler::class);

// register $app self and routeCollector.
$container->set(App::class, $app);
$container->set(RouteCollectorInterface::class, $app->getRouteCollector());

return $app;
