<?php

namespace WScore\Deca;

use Aura\Session\SessionFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Services\SessionAura;
use WScore\Deca\Services\Setting;
use WScore\Deca\Views\Twig\TwigLoader;
use WScore\Deca\Views\Twig\ViewTwig;

class Definitions
{
    public const APP_DIR = 'app-Dir';

    /**
     * @var callable[]
     */
    private array $definitions = [];

    public function __construct() {
        $this->load($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param callable[] $definitions
     * @return void
     */
    public function load(array $definitions): void
    {
        $this->definitions = array_merge($this->definitions, $definitions);
    }

    /**
     * set a value to the container.
     */
    public function setValue(string $name, mixed $value): static
    {
        $this->definitions[$name] = function() use ($value) { return $value;};
        return $this;
    }

    /**
     * set an alias to the container; alias can be a class name or a service name.
     */
    public function setAlias(string $name, string $alias): static
    {
        $this->definitions[$name] =
            function(ContainerInterface $container) use ($alias) {
            return $container->get($alias);
        };
        return $this;
    }

    public function get(string $name): ?callable
    {
        return $this->definitions[$name] ?? null;
    }

    private function getDefaults(): array
    {
        return [
            ResponseFactoryInterface::class => function () {
                return new Psr17Factory();
            },
            Setting::class => function(ContainerInterface $container) {
                return Setting::forge($container->get(self::APP_DIR) . '/../settings.ini', $_ENV);
            },
            ViewTwig::class => function(ContainerInterface $container) {
                $environment = $container->get(Environment::class);
                $view = new ViewTwig($environment);
                $loader = $container->get(TwigLoader::class);
                $view->setRuntimeLoader($loader);
                return $view;
            },
            SessionAura::class => function(ContainerInterface $container) {
                return new SessionAura($container->get(SessionFactory::class));
            },
            Messages::class => function(ContainerInterface $container) {
                return new Messages($container->get(SessionInterface::class));
            },
            Environment::class => function(ContainerInterface $container) {
                $appDir = $container->get(self::APP_DIR);
                $loader = new FilesystemLoader($appDir . '/templates/');
                return new Environment($loader, [
                    'cache' => $appDir . '/../var/cache',
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
            Mailer::class => function (ContainerInterface $c) {
                $settings = $c->get(Setting::class);
                $dsn = $settings->get('MAILER_DSN');
                $transport = $dsn
                    ? Transport::fromDsn($dsn)
                    : new SendmailTransport();

                return new Mailer($transport);
            },
        ];
    }
}