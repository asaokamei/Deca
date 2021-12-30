<?php
declare(strict_types=1);

namespace App\Routes\Controllers\Samples;

use App\Routes\Utils\AbstractController;

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