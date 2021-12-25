<?php
declare(strict_types=1);


use App\Application\Container\Setting;
use App\Application\Handlers\ErrorTwigRenderer;
use App\Application\Handlers\ErrorWhoopsRenderer;
use App\Application\Interfaces\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Handlers\ErrorHandler;

if (!isset($app) || !isset($request)) {
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
$setting = $container->get(Setting::class);

$displayErrorDetails = (bool) ($setting['display_errors'] ?? false);
$logger = $container->get(LoggerInterface::class);

/**
 * Create Error Handler
 */
$errorHandler = $container->get(ErrorHandler::class);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * set up Twig extension
 */
/** @var ViewInterface $twig */
$twig = $container->get(ViewInterface::class);
$twig->setRequest($request);
