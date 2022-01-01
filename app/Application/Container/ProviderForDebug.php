<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace App\Application\Container;


use App\Application\Handlers\ErrorWhoopsRenderer;
use App\Application\Interfaces\ProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use DI;
use Slim\Handlers\ErrorHandler;

class ProviderForDebug implements ProviderInterface
{
    public static function getDefinitions(): array
    {
        return [
            // define real objects
            Logger::class => DI\factory([self::class, 'getMonolog']),
            ErrorHandler::class => DI\factory([self::class, 'getErrorHandler']),
        ];
    }

    public static function getErrorHandler(ContainerInterface $c): ErrorHandler
    {
        $app = $c->get(App::class);

        $responseFactory = $app->getResponseFactory();
        $callableResolver = $app->getCallableResolver();
        $errorHandler = new ErrorHandler($callableResolver, $responseFactory, $c->get(LoggerInterface::class));
        $errorHandler->registerErrorRenderer('text/html', $c->get(ErrorWhoopsRenderer::class));
        $errorHandler->setDefaultErrorRenderer('text/html', $c->get(ErrorWhoopsRenderer::class));

        return $errorHandler;
    }

    public static function getMonolog(ContainerInterface $c): Logger
    {
        /** @var Setting $settings */
        $settings = $c->get(Setting::class);

        $logger = new Logger($settings['app_name']??'decaApp');

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        $path = $settings->projectRoot . '/var/app.log';

        $handler = new StreamHandler($path, Logger::DEBUG);
        $logger->pushHandler($handler);

        return $logger;
    }
}