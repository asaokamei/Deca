<?php

namespace Tests\End2End;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;
use PHPUnit\Framework\TestCase;

class TopPageTest extends TestCase
{
    private function createRequest(string $path = '/'): ServerRequestInterface
    {
        $request = ServerRequestCreatorFactory::create()
            ->createServerRequestFromGlobals();
        $uri = $request->getUri();
        $uri = $uri->withPath($path);
        $request = $request->withUri($uri);

        return $request;
    }

    private function createApp(ServerRequestInterface $request): App
    {
        require __DIR__ . '/../../appDemo/getContainer.php';
        require __DIR__ . '/../../appDemo/getApp.php';
        require __DIR__ . '/../../appDemo/routes.php';
        /** @var App $app */
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

    public function testTopPage()
    {
        $html = $this->getHtml('/');

        $this->assertStringContainsString('<title>Deca for PHP</title>', $html);
        $this->assertStringContainsString('Deca Framework</h1>', $html);
    }

    public function test404NotFound()
    {
        $response = $this->getResponse('/Path-NotFound');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDividedByZeroReturns500()
    {
        $response = $this->getResponse('/samples/errors/error');
        $this->assertEquals(500, $response->getStatusCode());

        $response->getBody()->rewind();
        $html = $response->getBody()->getContents();
        $this->assertStringContainsString('Slim Application Error', $html);
    }
}
