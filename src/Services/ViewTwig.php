<?php
declare(strict_types=1);

namespace WScore\Deca\Services;

use WScore\Deca\Interfaces\SessionInterface;
use WScore\Deca\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class ViewTwig implements ViewInterface
{
    private Environment $environment;
    private LoaderInterface $loader;

    /**
     * Default view variables
     *
     * @var array<string, mixed>
     */
    protected array $defaultVariables = [];
    /**
     * @var array
     */
    private array $info;

    /**
     * @param string $tempDir
     * @param array<string, mixed> $settings Twig environment settings
     * @param array $info
     */
    public function __construct(string $tempDir, array $settings = [], array $info = [])
    {
        $loader = new FilesystemLoader();
        $loader->setPaths($tempDir);

        $this->loader = $loader;
        $this->environment = new Environment($this->loader, $settings);
        $this->info = $info;
    }

    /**
     * Output rendered template
     *
     * @param ResponseInterface $response
     * @param string $template Template pathname relative to templates directory
     * @param array<string, mixed> $data Associative array of template variables
     *
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $response->getBody()->write($this->drawTemplate($template, $data));

        return $response;
    }

    /**
     * Fetch rendered template
     *
     * @param  string               $template Template pathname relative to templates directory
     * @param  array<string, mixed> $data     Associative array of template variables
     *
     * @throws LoaderError  When the template cannot be found
     * @throws SyntaxError  When an error occurred during compilation
     * @throws RuntimeError When an error occurred during rendering
     *
     * @return string
     */
    public function drawTemplate(string $template, array $data = []): string
    {
        $data = array_merge($this->defaultVariables, $data);
        return $this->environment->render($template, $data);
    }

    public function add(string $key, $value): void
    {
        $this->defaultVariables[$key] = $value;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
    }
}