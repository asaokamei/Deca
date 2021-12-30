<?php

namespace App\Application\Services;

use App\Application\Interfaces\RoutingInterface;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;

class Routing implements RoutingInterface
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;
    /**
     * @var RouteParserInterface
     */
    private $routeParser;

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