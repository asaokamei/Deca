<?php

namespace App\Application\Handlers;

use App\Application\Interfaces\ViewInterface;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class ErrorTwigRenderer implements ErrorRendererInterface
{
    /**
     * @var ViewInterface
     */
    private $view;

    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $title = $exception instanceof HttpException
            ? $exception->getTitle()
            : get_class($exception);
        return $this->view->fetch('layouts/error.twig', [
            'title' => $title,
        ]);
    }
}