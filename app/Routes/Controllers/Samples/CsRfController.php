<?php
declare(strict_types=1);


namespace App\Routes\Controllers\Samples;


use App\Routes\AbstractController;
use Psr\Http\Message\ResponseInterface;

class CsRfController extends AbstractController
{
    public function onGet(): ResponseInterface
    {
        return $this->view('samples/csrf.twig');
    }

    public function onPost(): ResponseInterface
    {
        $this->getMessages()->addSuccess('Post accepted!<br>CSRF Token validated...');
        return $this->view('samples/csrf.twig');
    }
}