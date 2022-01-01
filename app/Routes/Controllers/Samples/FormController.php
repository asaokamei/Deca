<?php
declare(strict_types=1);

namespace App\Routes\Controllers\Samples;


use App\Routes\Filters\PostArray;
use App\Routes\Filters\PostAsArgs;
use App\Routes\Utils\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[PostAsArgs]
#[PostArray(name: 'posted')]
class FormController extends AbstractController
{
    public function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig');
    }

    public function onPost($posted, $name): ResponseInterface
    {
        return $this->view('samples/form.twig', [
            'posts' => $posted,
            'name' => $name,
        ]);
    }
}