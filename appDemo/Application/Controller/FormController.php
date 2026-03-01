<?php
declare(strict_types=1);


namespace AppDemo\Application\Controller;


use AppDemo\Application\Forms\SampleLeanValidator;
use Psr\Http\Message\ResponseInterface;
use WScore\Deca\Controllers\AbstractController;

class FormController extends AbstractController
{
    public function __construct(SampleLeanValidator $validator)
    {
        $this->setValidator($validator);
    }

    public function onGet(): ResponseInterface
    {
        return $this->view('samples/form.twig');
    }

    public function onPost(): ResponseInterface
    {
        $inputs = $this->getInputs();

        if (isset($inputs['with_error']) && $inputs['with_error']) {
            $this->messages()->addError('Post with Error!<br>Showing default errors...');
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
            $this->withInputs($inputs, $errors);
            return $this->view('samples/form.twig');
        }
        if ($this->validate()->failed()) {
            if (isset($inputs['redirect'])) {
                $this->messages()->addError('Post is invalidated!<br>Redirected back to the input form...');
                return $this->redirect()->toRoute('samples-form');
            }
            $this->messages()->addError('Post is invalidated!<br>Check the Inputs...');
            return $this->view('samples/form.twig');
        }
        $this->messages()->addSuccess('Post accepted!<br>Input validated...');

        return $this->view('samples/form-done.twig');
    }
}