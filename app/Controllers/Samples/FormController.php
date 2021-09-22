<?php
declare(strict_types=1);

namespace App\Controllers\Samples;


use App\Controllers\AbstractController;
use App\Controllers\Filters\PostArray;
use Psr\Http\Message\ResponseInterface;

class FormController extends AbstractController
{
    public function __construct()
    {
        $this->addArgFilter(new PostArray());
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig', [
        ]);
    }

    public function onPost($posts): ResponseInterface
    {
        return $this->view('samples/form.twig', [
            'posts' => $posts
        ]);
    }
}