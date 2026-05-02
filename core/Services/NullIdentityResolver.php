<?php

declare(strict_types=1);

namespace WScore\Deca\Services;

use Psr\Http\Message\ServerRequestInterface;
use WScore\Deca\Contracts\IdentityInterface;
use WScore\Deca\Contracts\IdentityResolverInterface;

/**
 * Default resolver: always guest. Replace {@see IdentityResolverInterface} in the container for real auth.
 */
final class NullIdentityResolver implements IdentityResolverInterface
{
    public function resolve(ServerRequestInterface $request): ?IdentityInterface
    {
        return null;
    }
}
