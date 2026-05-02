<?php

declare(strict_types=1);

namespace Tests\Core\Unit\Middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\IdentityResolverInterface;
use WScore\Deca\Middleware\ResolveIdentityMiddleware;

class ResolveIdentityMiddlewareTest extends TestCase
{
    public function testSetsIdentityAttributeFromResolver(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $resolver = $this->createMock(IdentityResolverInterface::class);
        $resolver->method('resolve')->willReturn($identity);

        $middleware = new ResolveIdentityMiddleware($resolver);
        $request = new ServerRequest('GET', '/');

        $bag = new \stdClass();
        $handler = new class ($bag) implements RequestHandlerInterface {
            public function __construct(private \stdClass $bag)
            {
            }

            public function handle(ServerRequestInterface $request): Response
            {
                $this->bag->attribute = $request->getAttribute(IdentityInterface::class);

                return new Response();
            }
        };

        $middleware->process($request, $handler);
        $this->assertSame($identity, $bag->attribute);
    }

    public function testSetsNullForGuest(): void
    {
        $resolver = $this->createMock(IdentityResolverInterface::class);
        $resolver->method('resolve')->willReturn(null);

        $middleware = new ResolveIdentityMiddleware($resolver);
        $request = new ServerRequest('GET', '/');

        $bag = new \stdClass();
        $handler = new class ($bag) implements RequestHandlerInterface {
            public function __construct(private \stdClass $bag)
            {
            }

            public function handle(ServerRequestInterface $request): Response
            {
                $this->bag->attribute = $request->getAttribute(IdentityInterface::class);

                return new Response();
            }
        };

        $middleware->process($request, $handler);
        $this->assertNull($bag->attribute);
    }
}
