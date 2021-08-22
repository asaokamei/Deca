<?php


namespace App\Controllers\Samples;


use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;

class CsRfController extends AbstractController
{
    public function onGet(): ResponseInterface
    {
        return $this->view('samples/csrf.twig');
    }

    public function onPost(): ResponseInterface
    {
        $this->flashMessage('Post accepted!<br>CSRF Token validated...');
        return $this->view('samples/csrf.twig');
    }
}