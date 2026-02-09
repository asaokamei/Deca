<?php

namespace WScore\Deca\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

interface ViewInterface
{
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
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface;

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
    public function drawTemplate(string $template, array $data = []): string;

    /**
     * add a default value.
     *
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function add(string $key, $value): void;

    public function setRequest(ServerRequestInterface $request): void;

    public function setInputs(array $inputs, array $errors = []): void;
}