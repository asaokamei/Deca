<?php

namespace Tests\End2End;

use App\AppBuilder;
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
        return AppBuilder::forge(dirname(__DIR__, 2))
            ->loadSettings(__DIR__.'/settings.test.ini')
            ->loadContainer(false)
            ->build($request);
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

        $this->assertStringContainsString('<h1>Deca Demo</h1>', $html);
        $this->assertStringContainsString('<p>This is Deca PHP...</p>', $html);
    }

    public function test404NotFound()
    {
        $response = $this->getResponse('/Path-NotFound');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDividedByZeroReturns500()
    {
        $response = $this->getResponse('/errors/div0');
        $this->assertEquals(500, $response->getStatusCode());

        $response->getBody()->rewind();
        $html = $response->getBody()->getContents();
        $this->assertStringContainsString('<h1>Sorry!</h1>', $html);
    }
}
