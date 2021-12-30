<?php
declare(strict_types=1);


use App\Application\Container\Setting;
use App\Application\Interfaces\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\RouteCollectorInterface;

if (!isset($this) || !isset($app) || !isset($request)) {
    return;
}
if (!$this instanceof \App\AppBuilder){
    return;
}
if (!$app instanceof App){
    return;
}
if (!$request instanceof ServerRequestInterface){
    return;
}

/** @var Setting $setting */
$container = $app->getContainer();

$this->getContainerBuilder()->set(App::class, $app); // register $app self.
$this->getContainerBuilder()->set(RouteCollectorInterface::class, $app->getRouteCollector()); // register $app self.

/**
 * Add Routing Middleware
 */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 */
$setting = $container->get(Setting::class);
$displayErrorDetails = (bool) ($setting['display_errors'] ?? false);
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

$errorHandler = $container->get(ErrorHandler::class);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * set up Twig extension
 */
/** @var ViewInterface $twig */
$twig = $container->get(ViewInterface::class);
$twig->setRequest($request);
