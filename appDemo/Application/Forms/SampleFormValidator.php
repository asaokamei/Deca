<?php

namespace AppDemo\Application\Forms;

use WScore\Deca\Validation\AbstractAuraValidator;

class SampleFormValidator extends AbstractAuraValidator
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function buildRules(?array $data = null): void
    {
        if ($data !== null) {
            return;
        }
        $this->filter = $this->factory->newSubjectFilter();
        $this->field('name')
            ->isNotBlank()
            ->setMessage('Name is Required');

        $this->field('say')
            ->isBlankOr('equalToValue', 'yeah')
            ->setMessage('Say Yeah! if you like');

        $this->field('language')
            ->isBlankOr('inValues', ['en', 'ja'])
            ->setMessage('Select language');

        $this->field('framework')
            ->isNotBlank()
            ->is('inValues', ['LARAVEL', 'SYMFONY', 'SLIM'])
            ->setMessage('Select Framework');

        $this->field('ai')
            ->isNotBlank()
            ->is('arrayValues', ['CHATGPT', 'GEMINI', 'CLAUDE'])
            ->setMessage('Select AI');

        $this->field('email')
            ->is('email')
            ->isNotBlank()
            ->setMessage('Input valid email address');

        $this->field('birthday')
            ->is('dateTime')
            ->isNotBlank()
            ->setMessage('Input birthday');

        $this->field('note', false);
    }
}