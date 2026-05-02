<?php

declare(strict_types=1);

namespace WScore\Deca\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Builds the response when a route requires an identity but the request has none.
 *
 * Override by binding a custom implementation in the container (redirect to login, JSON 401, etc.).
 */
interface IdentityUnauthorizedHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
