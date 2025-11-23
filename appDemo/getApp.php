<?php

use App\Application\Middleware\AppMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use WScore\Deca\Middleware\CsRfGuard;
use WScore\Deca\Services\Setting;

/** @var ContainerInterface $container */
if (!isset($container)) {
    return null;
}
if (!$container instanceof ContainerInterface) {
    return null;
}
$settings = $container->get(Setting::class);
$app = AppFactory::createFromContainer($container);

// set up middlewares
$app->addRoutingMiddleware();
$app->add(CsRfGuard::class);
$app->add(AppMiddleware::class);

$displayErrorDetails = (bool) ($settings['display_errors'] ?? false);
$app->addErrorMiddleware($displayErrorDetails, true, true);

$container->set(App::class, $app);

return $app;
