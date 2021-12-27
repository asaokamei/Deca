<?php

namespace App\Routes\Utils;

use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;

class Redirect
{
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var RouteParserInterface
     */
    private $routeParser;

    public function __construct(RouteParserInterface $routeParser, ResponseInterface $response)
    {
        $this->response = $response;
        $this->routeParser = $routeParser;
    }

    public function getUrlFor(string $string, $options = [], $query = []): string
    {
        return $this->routeParser->urlFor($string, $options, $query);
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