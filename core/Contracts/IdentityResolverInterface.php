<?php

declare(strict_types=1);

namespace WScore\Deca\Contracts;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves the current {@see IdentityInterface} from the request (session, JWT, etc.).
 *
 * Override by binding a custom implementation to this interface in the container.
 */
interface IdentityResolverInterface
{
    public function resolve(ServerRequestInterface $request): ?IdentityInterface;
}
