<?php

namespace App\Controllers\Samples;

use App\Controllers\AbstractController;

class SyntaxErrorController extends AbstractController
{
    public function onGet()
    {
        a // syntax error
    }
}