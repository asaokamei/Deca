<?php

namespace WScore\Deca;

use App\Application\Middleware\AppMiddleware;
use Aura\Session\SessionFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Interfaces\MessageInterface;
use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Interfaces\ViewInterface;
use WScore\Deca\Services\SessionAura;
use WScore\Deca\Services\Setting;
use WScore\Deca\Services\ViewTwig;

class Definitions
{
    /**
     * @var callable[]
     */
    private array $definitions = [];

    public function __construct() {
        $this->setupDefinitions();
    }

    /**
     * @return array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function set(string $name, callable $func): self
    {
        $this->definitions[$name] = $func;
        return $this;
    }

    public function get(string $name): ?callable
    {
        return $this->definitions[$name] ?? null;
    }

    private function setupDefinitions(): void
    {
        $this->definitions = [
            ResponseFactoryInterface::class => function () {
                return new Psr17Factory();
            },
            Setting::class => function() {
                return Setting::forge(__DIR__ . '/../settings.ini', $_ENV);
            },
            ViewInterface::class => function() {
                return new ViewTwig(__DIR__ . '/../templates', [
                    'cache' => __DIR__ . '/../var/cache/twig',
                ]);
            },
            SessionInterface::class => function(ContainerInterface $container) {
                return new SessionAura($container->get(SessionFactory::class));
            },
            MessageInterface::class => function(ContainerInterface $container) {
                return new Messages($container->get(SessionInterface::class));
            },
            Environment::class => function() {
                $loader = new FilesystemLoader(__DIR__ . '/templates/');
                return new Environment($loader, [
                    'cache' => __DIR__ . '/../var/cache',
                    'auto_reload' => true,
                ]);
            },
            PDO::class => function (ContainerInterface $c) {
                $settings = $c->get(Setting::class);
                return new PDO(
                    $settings['PDO_DSN'],
                    $settings['PDO_USER'],
                    $settings['PDO_PASS'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            },
            MailerInterface::class => function (ContainerInterface $c) {
                $settings = $c->get(Setting::class);
                $dsn = $settings->get('MAILER_DSN');
                $transport = $dsn
                    ? Transport::fromDsn($dsn)
                    : new SendmailTransport();

                return new Mailer($transport);
            },
            App::class => function(ContainerInterface $container) {
                $settings = $container->get(Setting::class);
                $app = AppFactory::createFromContainer($container);
                $app->addRoutingMiddleware();
                $app->add(AppMiddleware::class);
                $displayErrorDetails = (bool) ($settings['display_errors'] ?? false);
                $app->addErrorMiddleware($displayErrorDetails, true, true);

                return $app;
            },
            RouteCollectorInterface::class => function(ContainerInterface $container) {
                $app = $container->get(App::class);
                return $app->getRouteCollector();
            },

        ];
    }
}