<?php

declare(strict_types=1);

namespace WScore\Deca\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\IdentityUnauthorizedHandlerInterface;

/**
 * Continues only when {@see IdentityInterface::class} is a non-null {@see IdentityInterface} instance.
 */
final class RequireIdentityMiddleware implements MiddlewareInterface
{
    public function __construct(private IdentityUnauthorizedHandlerInterface $unauthorizedHandler)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $request->getAttribute(IdentityInterface::class);
        if (!$identity instanceof IdentityInterface) {
            return $this->unauthorizedHandler->handle($request);
        }

        return $handler->handle($request);
    }
}
