<?php

namespace App\Routes\Utils;

use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class Respond
{
    const OK = 200;

    private ContainerInterface $container;

    private ResponseInterface $response;

    public function __construct(ContainerInterface $container, ResponseInterface $response)
    {
        $this->container = $container;
        $this->response = $response;
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
        /** @noinspection PhpUnhandledExceptionInspection*/
        $this->container->get(SessionInterface::class)->clearFlash(); // rendering a view means ...
        /** @noinspection PhpUnhandledExceptionInspection*/
        $view = $this->container->get(ViewInterface::class);
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