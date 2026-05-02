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

if (!function_exists('getApp')) {
    function getApp(ContainerInterface $container): App
    {
        $app = AppFactory::createFromContainer($container);

        // set up middlewares (Slim runs outermost `add` first; see middleware-session-csrf docs).
        // Optional auth: bind IdentityResolverInterface in the container, then e.g.
        // $app->add(\WScore\Deca\Middleware\ResolveIdentityMiddleware::class); // sets WScore\Deca\Contracts\IdentityInterface on the request (null = guest).
        // Use RequireIdentityMiddleware only on routes/groups that require a logged-in user.
        $app->addRoutingMiddleware();
        $app->add(CsRfGuard::class);
        $app->add(AppMiddleware::class);

        $settings = $container->get(Setting::class);
        $displayErrorDetails = $settings->isDebug();
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
}


