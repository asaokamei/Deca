<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Interfaces\SessionInterface;
use App\Application\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use App\Application\Services\Twig\TwigExtension;
use App\Application\Services\Twig\TwigRuntimeLoader;

class ViewTwig implements ViewInterface
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * Default view variables
     *
     * @var array<string, mixed>
     */
    protected $defaultVariables = [];
    /**
     * @var array
     */
    private $info;

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
        $response->getBody()->write($this->fetch($template, $data));

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
    public function fetch(string $template, array $data = []): string
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
        $runtimeLoader = new TwigRuntimeLoader($this->info[App::class], $request, $this->info[SessionInterface::class]);
        $this->environment->addRuntimeLoader($runtimeLoader);

        $extension = new TwigExtension();
        $this->environment->addExtension($extension);
    }
}