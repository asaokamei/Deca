<?php

namespace App\Application\Handlers;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorWhoopsRenderer implements ErrorRendererInterface
{
    /**
     * @param Throwable $exception
     * @param bool      $displayErrorDetails
     * @return string
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        return $whoops->handleException($exception);
    }
}