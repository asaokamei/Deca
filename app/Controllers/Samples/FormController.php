<?php
declare(strict_types=1);

namespace App\Controllers\Samples;


use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormController extends AbstractController
{
    /**
     * @return ResponseInterface
     */
    protected function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig', [
            'form' => 'not yet ready',
        ]);
    }

}