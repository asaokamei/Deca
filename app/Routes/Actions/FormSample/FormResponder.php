<?php

namespace App\Routes\Actions\FormSample;

use App\Routes\Utils\AbstractResponder;
use Psr\Http\Message\ResponseInterface;

class FormResponder extends AbstractResponder
{
    public function view(array $data = []): ResponseInterface
    {
        return $this->respond()->view('samples/form.twig', $data);
    }

}