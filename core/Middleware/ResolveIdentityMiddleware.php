<?php

declare(strict_types=1);

namespace WScore\Deca\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\IdentityResolverInterface;

/**
 * Runs {@see IdentityResolverInterface} and sets {@see IdentityInterface::class} on the request (null for guest).
 */
final class ResolveIdentityMiddleware implements MiddlewareInterface
{
    public function __construct(private IdentityResolverInterface $identityResolver)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $this->identityResolver->resolve($request);
        $request = $request->withAttribute(IdentityInterface::class, $identity);

        return $handler->handle($request);
    }
}
