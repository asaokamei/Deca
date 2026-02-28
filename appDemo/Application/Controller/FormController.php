<?php
declare(strict_types=1);


namespace AppDemo\Application\Controller;


use AppDemo\Application\Forms\SampleLeanValidator;
use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;

class FormController extends AbstractController
{
    public function __construct(private SampleLeanValidator $validator)
    {
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig');
    }

    public function onPost(): ResponseInterface
    {
        $inputs = $this->getInputs();

        if (isset($inputs['with_error']) && $inputs['with_error']) {
            $this->messages()->addError('Post with Error!<br>Showing inputs and errors...');
            $errors = [
                'name' => 'This is an error message for name.',
                'note' => 'This is an error message for note.',
                'language' => 'This is an error message for language.',
                // use dot-notation keys to match form names and FormDotted
                'profile.email' => 'This is an error message for email.',
                'dev.ai' => 'This is an error message for AI.',
                'dev.ai.1' => 'AI error for Gemini only.',
                'say' => 'This is an error message for YEAH!',
                'dev.framework' => 'This is an error message for framework.',
                'profile.birthday' => 'This is an error message for birthday.',
            ];
        } elseif ($this->validator->validate($inputs)->success()) {
            $this->messages()->addSuccess('Post accepted!<br>Input validated...');
            $errors = [];
        } else {
            $this->messages()->addError('Post rejected!<br>Input not validated...');
            $errors = $this->validator->getErrors();
        }

        return $this->respond()
            ->withInputs($inputs, $errors)
            ->view('samples/form.twig');
    }
}