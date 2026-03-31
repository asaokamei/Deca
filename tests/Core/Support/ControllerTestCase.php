<?php

namespace Tests\Core\Support;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Services\Routing;
use WScore\Deca\Services\SessionAura;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Views\Twig\ViewTwig;
use WScore\Deca\Services\Setting;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Aura\Session\SessionFactory;
use Aura\Session\Phpfunc;

abstract class ControllerTestCase extends TestCase
{
    protected App $app;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../appDemo/getApp.php';
        $this->container = $this->createContainer();
        $this->app = \getApp($this->container);
    }

    protected function createContainer(): ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            Setting::class => new Setting([
                'display_errors' => true,
            ]),
            LoggerInterface::class => new NullLogger(),
            SessionInterface::class => function () {
                $sessionFactory = new SessionFactory();
                // SessionAura expects SessionFactory as argument 1
                return new SessionAura($sessionFactory);
            },
            MessageInterface::class => function (ContainerInterface $c) {
                return new Messages($c->get(SessionInterface::class));
            },
            ViewInterface::class => function () {
                $loader = new ArrayLoader([
                    'test.twig' => 'Hello {{ name }}!',
                ]);
                $twig = new Environment($loader);
                return new ViewTwig($twig);
            },
            RoutingInterface::class => function (ContainerInterface $c) {
                return new Routing($c->get(\Slim\App::class)->getRouteCollector());
            },
        ]);
        return $builder->build();
    }

    protected function createRequest(
        string $method,
        string $path,
        array $params = [],
        array $headers = ['Content-Type' => 'application/x-www-form-urlencoded']
    ): ServerRequestInterface {
        $factory = new Psr17Factory();
        $uri = 'http://localhost' . $path;
        $request = $factory->createServerRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($method === 'POST') {
            $request = $request->withParsedBody($params);
        } else {
            $request = $request->withQueryParams($params);
        }

        return $request;
    }

    protected function callAction(string $controllerClass, ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $controller = $this->container->get($controllerClass);
        $response = (new Psr17Factory())->createResponse();
        
        // AbstractController::__invoke expects (Request, Response, Args)
        return $controller($request, $response, $args);
    }
}

class PhpfuncMock extends Phpfunc
{
    public function __construct() {}
    public function session_start(array $options = []): bool { return true; }
    public function session_status(): int { return PHP_SESSION_ACTIVE; }
    public function session_id(?string $id = null): string { return 'test-session-id'; }
    public function header($string, $replace = true, $http_response_code = null): void {}
    public function setcookie($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false): bool { return true; }
}
