<?php

namespace Tests\Demo\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;

class TopPageTest extends HttpTestCase
{
    private function getResponse(string $path = '/'): ResponseInterface
    {
        $request = $this->createRequest('GET', $path);
        $app = $this->createApp();
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
