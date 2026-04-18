<?php

namespace Tests\Http\Demo;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;
class TopPageTest extends TestCase
{
    private function createRequest(string $path = '/'): ServerRequestInterface
    {
        $request = ServerRequestCreatorFactory::create()
            ->createServerRequestFromGlobals();
        $uri = $request->getUri()->withPath($path);
        return $request->withUri($uri);
    }

    private function createApp(ServerRequestInterface $request): App
    {
        require_once __DIR__ . '/../../../appDemo/boot.php';
        $settingsIniPath = __DIR__ . '/../../../settings.ini';
        $setting = getSettings($settingsIniPath);
        $definitions = getDefinitions($setting);
        $container = getContainer($definitions);
        $app = getApp($container);
        registerRoutes($app);

        return $app;
    }

    private function getResponse(string $path = '/'): ResponseInterface
    {
        $request = $this->createRequest($path);
        $app = $this->createApp($request);
        return $app->handle($request);
    }

    private function getHtml(string $path = '/'): string
    {
        $response = $this->getResponse($path);
        $response->getBody()->rewind();
        return $response->getBody()->getContents();
    }

    public function testTopPage(): void
    {
        $html = $this->getHtml('/');

        $this->assertStringContainsString('<h1>Deca Framework</h1>', $html);
        $this->assertStringContainsString('Deca Framework</h1>', $html);
    }

    public function test404NotFound(): void
    {
        $response = $this->getResponse('/Path-NotFound');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDividedByZeroReturns500(): void
    {
        $response = $this->getResponse('/samples/errors/error');
        $this->assertEquals(500, $response->getStatusCode());

        $response->getBody()->rewind();
        $html = $response->getBody()->getContents();
        $this->assertStringContainsString('Slim Application Error', $html);
    }
}
