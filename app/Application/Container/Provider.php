<?php
declare(strict_types=1);

namespace App\Application\Container;


use App\Application\Interfaces\MessageInterface;
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
use function DI\get;

class Provider
{
    public function getDefinitions(): array
    {
        $list = [
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            Psr17Factory::class => 'getPsr17Factory',
            LoggerInterface::class => 'getMonolog',
            ViewInterface::class => 'getView',
            SessionInterface::class => 'getSession',
            MessageInterface::class => 'getMessage',

            'view' => get(ViewInterface::class),
        ];
        return $this->prepare($list);
    }

    private function prepare(array $list): array
    {
        foreach ($list as $key => $item) {
            if (is_string($item)) {
                $list[$key] = function(ContainerInterface $c) use($item) {
                    return $this->$item($c);
                };
            }
        }
        return $list;
    }

    private function getPsr17Factory()
    {
        return new Psr17Factory();
    }

    private function getMonolog(ContainerInterface $c): LoggerInterface
    {
        /** @var Setting $settings */
        $settings = $c->get('settings');
        $isProduction = $settings->isProduction();

        $logger = new Logger($settings['app_name']??'decaApp');

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        $path = $settings['projectRoot'] . '/var/app.log';

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

    private function getView(ContainerInterface $c): ViewInterface
    {
        /** @var Setting $settings */
        $settings = $c->get('settings');

        $tempDir = $settings['projectRoot'] . '/app/templates';
        $cacheDir = $settings['cacheDirectory'] . '/twig';

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

    private function getSession(ContainerInterface $c)
    {
        return new SessionAura(new SessionFactory());
    }

    private function getMessage(ContainerInterface $c)
    {
        return new MessageAura(new SessionFactory());
    }
}