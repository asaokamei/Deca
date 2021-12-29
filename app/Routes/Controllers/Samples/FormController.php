<?php
declare(strict_types=1);

namespace App\Routes\Controllers\Samples;


use App\Routes\Filters\PostArray;
use App\Routes\Utils\AbstractController;
use Psr\Http\Message\ResponseInterface;

class FormController extends AbstractController
{
    public function __construct(PostArray $postArray)
    {
        $this->addArgFilter($postArray);
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