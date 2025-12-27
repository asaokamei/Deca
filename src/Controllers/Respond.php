<?php

namespace WScore\Deca\Controllers;

use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Interfaces\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Respond
{
    const OK = 200;

    private ContainerInterface $container;

    private ResponseInterface $response;

    private ServerRequestInterface $request;
    private ViewInterface $view;

    public function __construct(ContainerInterface $container, ResponseInterface $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
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

    private function getView(): ViewInterface
    {
        if (!isset($this->view)) {
            $this->view = $this->container->get(ViewInterface::class);
            $this->view->setRequest($this->request);
            $this->container->get(SessionInterface::class)->clearFlash();
        }
        return $this->view;
    }

    public function view(string $template, array $data = []): ResponseInterface
    {
        $view = $this->getView();
        return $view->render($this->response, $template, $data);
    }

    public function drawTemplate(string $template, array $data = []): string
    {
        $view = $this->getView();
        return $view->drawTemplate($template, $data);
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

    public function withInputs(array $inputs, array $errors = []): static
    {
        $this->getView()->setInputs($inputs, $errors);
        return $this;
    }
}