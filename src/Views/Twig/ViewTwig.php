<?php

namespace WScore\Deca\Views\Twig;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use WScore\Deca\Interfaces\ViewInterface;

class ViewTwig implements ViewInterface
{
    /**
     * list of loaders
     * @var TwigLoaderInterface[]
     */
    private array $loaders = [];

    private bool $isLoaded = false;

    private ServerRequestInterface $request;

    public function __construct(
        private Environment $environment,
        private array $defaultVariables = []
    ) {
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $response->getBody()->write($this->drawTemplate($template, $data));
        return $response;
    }

    public function drawTemplate(string $template, array $data = []): string
    {
        $this->load();
        $data = array_merge($this->defaultVariables, $data);
        return $this->environment->render($template, $data);
    }

    public function add(string $key, $value): void
    {
        $this->defaultVariables[$key] = $value;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    private function load(): void
    {
        if ($this->isLoaded) {
            return;
        }
        foreach ($this->loaders as $loader) {
            if (isset($this->request)) {
                $loader->setRequest($this->request);
            }
            $loader->load($this->environment);
        }
        $this->isLoaded = true;
    }

    public function setRuntimeLoader(TwigLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }
}