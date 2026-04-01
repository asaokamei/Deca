<?php

namespace Tests\Core\Unit\Controllers;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use WScore\Deca\Controllers\Respond;

class RespondTest extends TestCase
{
    public function testResponse(): void
    {
        $factory = new Psr17Factory();
        $baseResponse = $factory->createResponse();
        $respond = new Respond($baseResponse);

        $response = $respond->response('Hello', 201, ['X-Test' => 'Value']);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Hello', (string)$response->getBody());
        $this->assertEquals('Value', $response->getHeaderLine('X-Test'));
    }

    public function testJson(): void
    {
        $factory = new Psr17Factory();
        $baseResponse = $factory->createResponse();
        $respond = new Respond($baseResponse);

        $response = $respond->json(['key' => 'value']);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"key":"value"}', (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testDownload(): void
    {
        $factory = new Psr17Factory();
        $baseResponse = $factory->createResponse();
        $respond = new Respond($baseResponse);

        $response = $respond->download('content', 'test.txt');
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('content', (string)$response->getBody());
        $this->assertStringContainsString('attachment; filename="test.txt"', $response->getHeaderLine('Content-Disposition'));
        $this->assertEquals('7', $response->getHeaderLine('Content-Length'));
        $this->assertEquals('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }
}
