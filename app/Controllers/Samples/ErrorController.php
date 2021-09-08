<?php

namespace App\Controllers\Samples;

use App\Controllers\AbstractController;

class ErrorController extends AbstractController
{
    protected function determineMethod(): string
    {
        if (isset($this->getArgs()['method'])) {
            return $this->getArgs()['method'];
        }
        return 'get';
    }

    public function onGet()
    {
        $x = 5/0;
    }
}