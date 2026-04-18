<?php

namespace WScore\Deca;

use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Nyholm\Psr7\Factory\Psr17Factory;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Services\Session;
use WScore\Deca\Services\Setting;
use WScore\Deca\Views\Twig\TwigLoader;
use WScore\Deca\Views\Twig\ViewTwig;

class Definitions
{
    public const APP_DIR = 'app-Dir';
    public const VAR_DIR = 'var-Dir';

    /** Absolute path to the application settings.ini (used when {@see Setting} is resolved from this path). */
    public const SETTINGS_INI_PATH = 'settings-ini-path';

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
            Setting::class => function (ContainerInterface $container) {
                return Setting::forge($container->get(self::SETTINGS_INI_PATH), $_ENV);
            },
            ViewTwig::class => function(ContainerInterface $container) {
                $environment = $container->get(Environment::class);
                $view = new ViewTwig($environment);
                $loader = $container->get(TwigLoader::class);
                $view->setRuntimeLoader($loader);
                return $view;
            },
            Session::class => function() {
                return new Session();
            },
            SessionInterface::class => function(ContainerInterface $container) {
                return $container->get(Session::class);
            },
            Messages::class => function(ContainerInterface $container) {
                return new Messages($container->get(SessionInterface::class));
            },
            Environment::class => function(ContainerInterface $container) {
                $appDir = $container->get(self::APP_DIR);
                $varDir = $container->get(self::VAR_DIR);
                $loader = new FilesystemLoader($appDir . '/templates/');
                $settings = $container->get(Setting::class);
                $cache =  $settings->isProduction()
                    ? $varDir . '/cache'
                    : false;
                return new Environment($loader, [
                    'cache' => $cache,
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
            PHPMailer::class => function (ContainerInterface $c) {
                $settings = $c->get(Setting::class);
                $mailer = new PHPMailer();
                $mailer->isSMTP();
                $mailer->Host = $settings->get('MAILER_HOST');
                $mailer->SMTPAuth = true;
                $mailer->Username = $settings->get('MAILER_USER');
                $mailer->Password = $settings->get('MAILER_PASS');
                $mailer->SMTPSecure = 'tls';
                $mailer->Port = 587;
                return $mailer;
            },
            LoggerInterface::class => function(ContainerInterface $container) {
                $settings = $container->get(Setting::class);

                $logger = new Logger($settings['app_name']??'decaApp');

                $processor = new UidProcessor();
                $logger->pushProcessor($processor);

                $path = $container->get(self::VAR_DIR) . '/app.log';

                if ($settings->isProduction()) {
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
        ];
    }
}