<?php

declare(strict_types=1);

namespace WScore\Deca\Services;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WScore\Deca\Contracts\IdentityUnauthorizedHandlerInterface;

/**
 * Plain 401 with a short body. Replace {@see IdentityUnauthorizedHandlerInterface} for redirects or JSON APIs.
 */
final class DefaultIdentityUnauthorizedHandler implements IdentityUnauthorizedHandlerInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(401);
        $response->getBody()->write('Unauthorized');

        return $response;
    }
}
