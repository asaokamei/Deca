<?php


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
$setting = $container->get('settings');

/** @var bool $displayErrorDetails */
$displayErrorDetails = $setting['displayErrorDetails'] ?? false;
$logger = $container->get(LoggerInterface::class);

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$callableResolver = $app->getCallableResolver();
$errorHandler = new ErrorHandler($callableResolver, $responseFactory, $logger);
if ($setting->isDebug()) {
    $errorHandler->registerErrorRenderer('text/html', new ErrorWhoopsRenderer());
} else {
    $viewer = $container->get(ViewInterface::class);
    $errorHandler->registerErrorRenderer('text/html', new ErrorTwigRenderer($viewer));
}

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * set up Twig extension
 */
/** @var ViewInterface $twig */
$twig = $container->get(ViewInterface::class);
$twig->setRequest($request);
