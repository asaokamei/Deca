<?php

namespace AppDemo\Application\Action;

use WScore\Deca\Controllers\AbstractController;

class InfoAction extends AbstractController
{
    public function action()
    {
        phpinfo();
        exit;
    }
}