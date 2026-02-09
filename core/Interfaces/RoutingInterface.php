<?php

namespace WScore\Deca\Interfaces;

use Psr\Http\Message\UriInterface;

interface RoutingInterface
{
    public function getBasePath(): string;

    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string;

    public function fullUrlFor(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string;

    public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string;
}