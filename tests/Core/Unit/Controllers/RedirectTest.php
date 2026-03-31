<?php

namespace Tests\Core\Unit\Controllers;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Controllers\Redirect;

class RedirectTest extends TestCase
{
    public function testToUrl(): void
    {
        $factory = new Psr17Factory();
        $baseResponse = $factory->createResponse();
        $routing = $this->createMock(RoutingInterface::class);
        $redirect = new Redirect($routing, $baseResponse);

        $response = $redirect->toUrl('/path', ['a' => 'b']);
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/path?a=b', $response->getHeaderLine('Location'));
    }

    public function testToRoute(): void
    {
        $factory = new Psr17Factory();
        $baseResponse = $factory->createResponse();
        $routing = $this->createMock(RoutingInterface::class);
        $routing->expects($this->once())
            ->method('urlFor')
            ->with('home', ['id' => 1], ['q' => 'search'])
            ->willReturn('http://localhost/home/1?q=search');
        
        $redirect = new Redirect($routing, $baseResponse);

        $response = $redirect->toRoute('home', ['id' => 1], ['q' => 'search']);
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/home/1?q=search', $response->getHeaderLine('Location'));
    }
}
