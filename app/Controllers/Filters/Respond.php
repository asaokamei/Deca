<?php

namespace App\Controllers\Filters;

use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

class Respond
{
    const OK = 200;

    /**
     * @var App
     */
    private $app;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(App $app, ContainerInterface $container, ResponseInterface $response)
    {
        $this->app = $app;
        $this->container = $container;
        $this->response = $response;
    }

    protected function getSession(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    public function response(string $input, int $status, array $header = []): ResponseInterface
    {
        $response = $this->response->withStatus($status);
        $response->getBody()->write($input);
        foreach ($header as $key => $val) {
            $response = $response->withHeader($key, $val);
        }

        return $response;
    }

    public function view(string $template, array $data = []): ResponseInterface
    {
        $this->getSession()->clearFlash(); // rendering a view means ...
        $view = $this->app->getContainer()->get(ViewInterface::class);
        return $view->render($this->response, $template, $data);
    }

    public function json(array $json): ResponseInterface
    {
        return $this->response(json_encode($json), self::OK, ['Content-Type' => 'application/json']);
    }

    public function download(string $content, string $filename, $attach = true, $mime = null): ResponseInterface
    {
        $type = $attach ? 'attachment' : 'inline';
        $mime = $mime ?: 'application/octet-stream';
        $encoded = urlencode($filename);

        return $this->response(
            $content,
            self::OK, [
            'Content-Disposition' => "{$type}; filename=\"{$filename}\"; filename*=UTF-8''{$encoded}",
            'Content-Length'      => (string)strlen($content),
            'Content-Type'        => $mime,
        ]);
    }
}