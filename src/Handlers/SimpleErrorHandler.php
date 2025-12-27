<?php

namespace WScore\Deca\Handlers;

use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;
use WScore\Deca\Interfaces\ViewInterface;

class SimpleErrorHandler implements ErrorRendererInterface
{
    public function __construct(private ViewInterface $view)
    {
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string {
        $title = 'Error Reported';
        if ($exception instanceof HttpNotFoundException) {
            $title = 'URL Not Found';
        } elseif ($exception instanceof HttpForbiddenException) {
            $title = 'Access Not Allowed';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $title = 'Not Available URL';
        }
        if ($exception instanceof HttpException) {
            $request = $exception->getRequest();
            $this->view->setRequest($request);
        }

        try {
            return $this->view->drawTemplate('layouts/error.twig', [
                'title' => $title,
                'exception' => $exception,
                'displayErrorDetails' => $displayErrorDetails
            ]);
        } catch (Throwable $e) {
            return 'Error Reported: ' . $e->getMessage();
        }
    }
}