<?php

namespace App\Controllers\Filters;

use Psr\Http\Message\ResponseInterface;
use Slim\App;

class Redirect
{
    /**
     * @var App
     */
    private $app;
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(App $app, ResponseInterface $response)
    {
        $this->app = $app;
        $this->response = $response;
    }

    public function getUrlFor(string $string, $options = [], $query = []): string
    {
        $routeParser = $this->app->getRouteCollector()->getRouteParser();
        return $routeParser->urlFor($string, $options, $query);
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