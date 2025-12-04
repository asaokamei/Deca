<?php

namespace AppDemo\Application\Controller;

use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;

class ErrorController extends AbstractController
{
    protected function determineMethod(): string
    {
        if (isset($this->getArgs()['method'])) {
            return $this->getArgs()['method'];
        }
        return 'get';
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/error.twig', []);
    }

    public function onException()
    {
        throw new \Exception('This is a test exception.');
    }

    public function onError()
    {
        return 0/5;
    }
}