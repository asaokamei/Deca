<?php

namespace WScore\Deca\Services;

use WScore\Deca\Interfaces\RoutingInterface;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;

class Routing implements RoutingInterface
{
    private RouteCollectorInterface $routeCollector;
    private RouteParserInterface $routeParser;

    public function __construct(RouteCollectorInterface $routeCollector)
    {
        $this->routeCollector = $routeCollector;
        $this->routeParser = $routeCollector->getRouteParser();
    }

    public function getBasePath(): string
    {
        return $this->routeCollector->getBasePath();
    }

    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    public function fullUrlFor(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($uri, $routeName, $data, $queryParams);
    }

    public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->relativeUrlFor($routeName, $data, $queryParams);
    }
}