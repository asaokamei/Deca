<?php

namespace WScore\Deca\Controllers;

use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Respond
{
    const OK = 200;

    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
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