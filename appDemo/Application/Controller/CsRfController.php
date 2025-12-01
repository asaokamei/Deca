<?php
declare(strict_types=1);


namespace AppDemo\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;

class CsRfController extends AbstractController
{
    public function onGet(): ResponseInterface
    {
        return $this->view('samples/csrf.twig');
    }

    public function onPost(): ResponseInterface
    {
        $this->messages()->addSuccess('Post accepted!<br>CSRF Token validated...');
        return $this->view('samples/csrf.twig');
    }
}