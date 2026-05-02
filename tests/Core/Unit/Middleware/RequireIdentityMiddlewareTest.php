<?php

declare(strict_types=1);

namespace Tests\Core\Unit\Middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\IdentityUnauthorizedHandlerInterface;
use WScore\Deca\Middleware\RequireIdentityMiddleware;

class RequireIdentityMiddlewareTest extends TestCase
{
    public function testInvokesHandlerWhenIdentityPresent(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $request = (new ServerRequest('GET', '/'))->withAttribute(IdentityInterface::class, $identity);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn(new Response(200));

        $unauthorized = $this->createMock(IdentityUnauthorizedHandlerInterface::class);
        $unauthorized->expects($this->never())->method('handle');

        $middleware = new RequireIdentityMiddleware($unauthorized);
        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUnauthorizedWhenAttributeMissing(): void
    {
        $request = new ServerRequest('GET', '/');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $unauthorized = $this->createMock(IdentityUnauthorizedHandlerInterface::class);
        $unauthorized->expects($this->once())->method('handle')->with($request)->willReturn(new Response(401));

        $middleware = new RequireIdentityMiddleware($unauthorized);
        $response = $middleware->process($request, $handler);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUnauthorizedWhenAttributeNull(): void
    {
        $request = (new ServerRequest('GET', '/'))->withAttribute(IdentityInterface::class, null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $unauthorized = $this->createMock(IdentityUnauthorizedHandlerInterface::class);
        $unauthorized->expects($this->once())->method('handle')->willReturn(new Response(403));

        $middleware = new RequireIdentityMiddleware($unauthorized);
        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
    }
}
