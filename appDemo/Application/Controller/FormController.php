<?php
declare(strict_types=1);


namespace AppDemo\Application\Controller;


use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;

class FormController extends AbstractController
{
    public function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig');
    }

    public function onPost(): ResponseInterface
    {
        $this->messages()->addSuccess('Post accepted!<br>Input validated...');
        return $this->view('samples/form.twig');
    }
}