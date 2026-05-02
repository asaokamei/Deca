<?php

namespace Tests\Core\Unit\Views\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Services\Setting;
use WScore\Deca\Views\Twig\TwigLoader;

class TwigLoaderTest extends TestCase
{
    private function createContainerMock()
    {
        $container = $this->createMock(ContainerInterface::class);
        return $container;
    }

    public function testLoadAddsGlobalsFunctionsAndFilters(): void
    {
        $container = $this->createContainerMock();
        $app = $this->createMock(App::class);
        $setting = $this->createMock(Setting::class);
        $routes = $this->createMock(RoutingInterface::class);

        $container->method('get')->willReturnMap([
            [App::class, $app],
            [Setting::class, $setting],
            [RoutingInterface::class, $routes],
        ]);

        $twig = new Environment(new ArrayLoader([]));
        $loader = new TwigLoader($container);
        
        $loader->load($twig);

        $globals = $twig->getGlobals();
        $this->assertSame($app, $globals['_app']);
        $this->assertSame($setting, $globals['_setting']);
        $this->assertSame($routes, $globals['_routes']);

        $this->assertNotNull($twig->getFunction('csrfTokenName'));
        $this->assertNotNull($twig->getFunction('isUserLoggedIn'));
        $this->assertNotNull($twig->getFunction('is_granted'));
        $this->assertNotNull($twig->getFilter('arrayToString'));
    }

    public function testFilterArrayToString(): void
    {
        $loader = new TwigLoader($this->createContainerMock());
        $data = ['a', 'b', 'c'];
        
        $this->assertEquals('a<br>b<br>c', $loader->filterArrayToString($data));
        $this->assertEquals('a,b,c', $loader->filterArrayToString($data, ','));
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $loader->filterArrayToString($data, 'json'));
    }

    public function testFilterMailAddressArray(): void
    {
        $loader = new TwigLoader($this->createContainerMock());
        
        // String input
        $this->assertEquals('test@example.com', $loader->filterMailAddressArray('test@example.com'));
        $this->assertEquals('User <test@example.com>', $loader->filterMailAddressArray('test@example.com', 'User'));

        // Iterable input (numeric keys)
        $this->assertEquals('test1@example.com, test2@example.com', $loader->filterMailAddressArray(['test1@example.com', 'test2@example.com']));

        // Iterable input (associative keys: mail => name)
        $this->assertEquals('User One <test1@example.com>, User Two <test2@example.com>', $loader->filterMailAddressArray(['test1@example.com' => 'User One', 'test2@example.com' => 'User Two']));
    }

    public function testGetCsrfTokenNameAndValue(): void
    {
        $container = $this->createContainerMock();
        $session = $this->createMock(SessionInterface::class);
        $session->method('getCsRfTokenName')->willReturn('csrf_name');
        $session->method('getCsRfToken')->willReturn('csrf_value');

        $container->method('get')->with(SessionInterface::class)->willReturn($session);

        $loader = new TwigLoader($container);
        $this->assertEquals('csrf_name', $loader->getCsrfTokenName());
        $this->assertEquals('csrf_value', $loader->getCsrfTokenValue());
    }

    public function testGetFlashMessagesAndNotices(): void
    {
        $container = $this->createContainerMock();
        $messages = $this->createMock(MessageInterface::class);
        $messages->method('getMessages')->willReturnMap([
            [MessageInterface::LEVEL_SUCCESS, ['success message']],
            [MessageInterface::LEVEL_ERROR, ['error message']],
        ]);

        $container->method('get')->with(MessageInterface::class)->willReturn($messages);

        $loader = new TwigLoader($container);
        $this->assertEquals(['success message'], $loader->getFlashMessages());
        $this->assertEquals(['error message'], $loader->getFlashNotices());
    }

    public function testGetBasePath(): void
    {
        $container = $this->createContainerMock();
        $app = $this->createMock(App::class);
        $app->method('getBasePath')->willReturn('/base');

        $container->method('get')->with(App::class)->willReturn($app);

        $loader = new TwigLoader($container);
        $this->assertEquals('/base', $loader->getBasePath());
    }

    public function testGetCurrentUrl(): void
    {
        $loader = new TwigLoader($this->createContainerMock());
        
        // No request set
        $this->assertEquals('', $loader->getCurrentUrl());

        // With request
        $request = $this->createMock(ServerRequestInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/current/path');
        $request->method('getUri')->willReturn($uri);

        $loader->setRequest($request);
        $this->assertEquals('/current/path', $loader->getCurrentUrl());
    }

    public function testGetUrlFor(): void
    {
        $container = $this->createContainerMock();
        $app = $this->createMock(App::class);
        $routeCollector = $this->createMock(RouteCollectorInterface::class);
        $routeParser = $this->createMock(RouteParserInterface::class);

        $app->method('getRouteCollector')->willReturn($routeCollector);
        $routeCollector->method('getRouteParser')->willReturn($routeParser);
        $routeParser->method('urlFor')->with('route', ['id' => 1], ['q' => 's'])->willReturn('/url/route/1?q=s');

        $container->method('get')->with(App::class)->willReturn($app);

        $loader = new TwigLoader($container);
        $this->assertEquals('/url/route/1?q=s', $loader->getUrlFor('route', ['id' => 1], ['q' => 's']));
    }

    public function testIdentityHelpersAsGuest(): void
    {
        $loader = new TwigLoader($this->createContainerMock());
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(IdentityInterface::class)->willReturn(null);
        $loader->setRequest($request);

        $this->assertFalse($loader->isUserLoggedIn());
        $this->assertSame('', $loader->getDisplayName());
        $this->assertSame('', $loader->getUserId());
        $this->assertFalse($loader->isGranted('ROLE_USER'));
    }

    public function testIdentityHelpersWhenAuthenticated(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getId')->willReturn('id-1');
        $identity->method('getDisplayName')->willReturn('Display');
        $identity->method('getRoles')->willReturn(['ROLE_USER']);

        $loader = new TwigLoader($this->createContainerMock());
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(IdentityInterface::class)->willReturn($identity);
        $loader->setRequest($request);

        $this->assertTrue($loader->isUserLoggedIn());
        $this->assertSame('Display', $loader->getDisplayName());
        $this->assertSame('id-1', $loader->getUserId());
        $this->assertTrue($loader->isGranted('ROLE_USER'));
        $this->assertFalse($loader->isGranted('ROLE_ADMIN'));
    }

    public function testIsGrantedWithSubjectReturnsFalse(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getRoles')->willReturn(['ROLE_USER']);

        $loader = new TwigLoader($this->createContainerMock());
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(IdentityInterface::class)->willReturn($identity);
        $loader->setRequest($request);

        $this->assertFalse($loader->isGranted('ROLE_USER', new \stdClass()));
    }
}
