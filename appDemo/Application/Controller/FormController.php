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
        $inputs = $this->getInputs();
        $inputs['email'] = strtoupper($inputs['email']);

        if (isset($inputs['with_error']) && $inputs['with_error']) {
            $this->messages()->addError('Post with Error!<br>Showing inputs and errors...');
            $errors = [
                'name' => 'This is an error message for name.',
                'note' => 'This is an error message for note.',
                'language' => 'This is an error message for language.',
                'email' => 'This is an error message for email.',
                'ai' => 'This is an error message for AI.',
                'say' => 'This is an error message for YEAH!',
                'framework' => 'This is an error message for framework.',
                'birthday' => 'This is an error message for birthday.',
            ];
        } else {
            $this->messages()->addSuccess('Post accepted!<br>Input validated...');
            $errors = [];
        }

        return $this->respond()
            ->withInputs($inputs, $errors)
            ->view('samples/form.twig');
    }
}