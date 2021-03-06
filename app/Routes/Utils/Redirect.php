<?php

namespace App\Routes\Utils;

use App\Application\Interfaces\RoutingInterface;
use Psr\Http\Message\ResponseInterface;

class Redirect
{
    private ResponseInterface $response;

    private RoutingInterface $routeParser;

    public function __construct(RoutingInterface $routeParser, ResponseInterface $response)
    {
        $this->response = $response;
        $this->routeParser = $routeParser;
    }

    public function getUrlFor(string $string, $options = [], $query = []): string
    {
        return $this->routeParser->urlFor($string, $options, $query);
    }

    public function getRelativeUrlFor(string $string, $options = [], $query = []): string
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
        $url = $this->getUrlFor($string, $options, $query);

        return $this->toUrl($url);
    }

}