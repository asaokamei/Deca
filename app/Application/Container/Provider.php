<?php
declare(strict_types=1);

namespace App\Application\Container;


use App\Application\Interfaces\MessageInterface;
use App\Application\Interfaces\ProviderInterface;
use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use App\Application\Services\MessageAura;
use App\Application\Services\SessionAura;
use App\Application\Services\ViewTwig;
use Aura\Session\SessionFactory;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use DI;

class Provider implements ProviderInterface
{
    public static function getDefinitions(): array
    {
        return [
            // define real objects
            Psr17Factory::class => DI\create(Psr17Factory::class),
            Logger::class => DI\factory([self::class, 'getMonolog']),
            ViewTwig::class => DI\factory([self::class, 'getView']),
            SessionAura::class => DI\factory([self::class, 'getSessionAura']),
            MessageAura::class => DI\factory([self::class, 'getMessageAura']),

            // define interfaces
            ResponseFactoryInterface::class => DI\get(Psr17Factory::class),
            LoggerInterface::class => DI\get(Logger::class),
            ViewInterface::class => DI\get(ViewTwig::class),
            SessionInterface::class => DI\get(SessionAura::class),
            MessageInterface::class => DI\get(MessageAura::class),

            // define shortcut entries
            'view' => DI\get(ViewInterface::class),
            'session' => DI\get(SessionInterface::class),
            'message' => DI\get(MessageInterface::class),
        ];
    }

    public static function getMonolog(ContainerInterface $c): Logger
    {
        /** @var Setting $settings */
        $settings = $c->get(Setting::class);
        $isProduction = $settings->isProduction();

        $logger = new Logger($settings['app_name']??'decaApp');

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        $path = $settings->projectRoot . '/var/app.log';

        if ($isProduction) {
            $handler = new FingersCrossedHandler(
                new StreamHandler($path, Logger::DEBUG),
                Logger::ERROR,
                0,
                true,
                true,
                Logger::NOTICE
            );
        } else {
            $handler = new StreamHandler($path, Logger::DEBUG);
        }
        $logger->pushHandler($handler);

        return $logger;
    }

    public static function getView(ContainerInterface $c): ViewTwig
    {
        /** @var Setting $settings */
        $settings = $c->get(Setting::class);

        $tempDir = $settings->projectRoot . '/app/templates';
        $cacheDir = $settings->cacheDirectory . '/twig';

        $view = new ViewTwig($tempDir, [
            'cache' => $cacheDir,
            'auto_reload' => true,
        ], [
            SessionInterface::class => $c->get(SessionInterface::class),
            App::class => $c->get(App::class),
        ]);
        $view->add('settings', $settings);

        return $view;
    }

    public static function getSessionAura(ContainerInterface $c): SessionAura
    {
        $session = new SessionAura($c->get(SessionFactory::class));
        $session->setCsrfTokenName('_csrf_token');
        return $session;
    }

    public static function getMessageAura(ContainerInterface $c): MessageAura
    {
        return new MessageAura($c->get(SessionInterface::class));
    }
}