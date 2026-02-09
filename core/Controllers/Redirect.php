<?php

namespace WScore\Deca\Controllers;

use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Interfaces\RoutingInterface;

class Redirect
{
    private ResponseInterface $response;

    private RoutingInterface $routeParser;

    public function __construct(RoutingInterface $routeParser, ResponseInterface $response)
    {
        $this->response = $response;
        $this->routeParser = $routeParser;
    }

    public function urlFor(string $string, $options = [], $query = []): string
    {
        return $this->routeParser->urlFor($string, $options, $query);
    }

    public function relativeUrlFor(string $string, $options = [], $query = []): string
    {
        return $this->routeParser->relativeUrlFor($string, $options, $query);
    }

    public function toUrl(string $url, array $query = []): ResponseInterface
    {
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    public function toRoute(string $string, $options = [], $query = []): ResponseInterface
    {
        $url = $this->urlFor($string, $options, $query);

        return $this->toUrl($url);
    }

}