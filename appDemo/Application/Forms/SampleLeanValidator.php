<?php

namespace AppDemo\Application\Forms;

use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Validation\AbstractLeanValidator;
use Wscore\LeanValidator\Validator;

class SampleLeanValidator extends AbstractLeanValidator
{
    protected function validation(array $data): ValidatorResultInterface
    {
        $this->sanitizer->toLower('email');
        $this->sanitizer->toHankaku('email');
        $this->sanitizer->toZenkaku('name');
        $cleanedData = $this->sanitizer->clean($data);

        $v = $this->buildValidator($cleanedData);
        $v->field('name', 'Name is Required')->required()->string();
        $v->field('say', 'Say Yeah! if you like')->optional()->equalTo('yeah');
        $v->field('language', 'Select language')->required()->in(['en', 'ja']);
        $v->field('dev')->asObject(function(Validator $v) {
            $v->field('framework', 'Select a Framework')->required()->in(['LARAVEL', 'SYMFONY', 'SLIM']);
            $v->field('ai', 'Select at least one AI')->required()->arrayCount(1)->asList('in', ['CHATGPT', 'GEMINI', 'CLAUDE']);
        });
        $v->field('profile')->asObject(function(Validator $v) {
            $v->field('email', 'Input valid email address')->required()->email();
            $v->field('birthday', 'Input birthday')->required()->date();
        });
        $v->field('note')->optional()->string();

        return $this->buildResult($v);
    }
}
