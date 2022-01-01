<?php
declare(strict_types=1);

namespace App\Routes\Actions\FormSample;


use App\Routes\Filters\PostArray;
use App\Routes\Filters\PostAsArgs;
use App\Routes\Utils\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[PostAsArgs]
#[PostArray(name: 'posted')]
class FormController extends AbstractController
{
    private FormResponder $responder;

    public function __construct(FormResponder $responder)
    {
        $this->responder = $responder;
    }

    public function onGet(): ResponseInterface
    {
        return $this->responder->view();
    }

    public function onPost($posted, $name): ResponseInterface
    {
        return $this->responder->view([
            'posts' => $posted,
            'name' => $name,
        ]);
    }
}