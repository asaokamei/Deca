<?php

namespace AppDemo\Application\Forms;

use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Validation\AbstractLeanValidator;
use Wscore\LeanValidator\Validator;

class SampleLeanValidator extends AbstractLeanValidator
{
    public function validate(array $data): ValidatorResultInterface
    {
        $this->sanitizer->toLower('email');
        $this->sanitizer->toHankaku('email');
        $this->sanitizer->toZenkaku('name');
        $cleanedData = $this->sanitizer->clean($data);

        $v = $this->buildValidator($cleanedData);
        $v->forKey('name', 'Name is Required')->required()->string();
        $v->forKey('say', 'Say Yeah! if you like')->optional()->equalTo('yeah');
        $v->forKey('language', 'Select language')->required()->in(['en', 'ja']);
        $v->forKey('dev')->nest(function(Validator $v) {
            $v->forKey('framework', 'Select Framework')->required()->in(['LARAVEL', 'SYMFONY', 'SLIM']);
            $v->forKey('ai', 'Select AI')->required()->arrayCount(1)->arrayApply('in', ['CHATGPT', 'GEMINI', 'CLAUDE']);
        });
        $v->forKey('profile')->nest(function(Validator $v) {
            $v->forKey('email', 'Input valid email address')->required()->email();
            $v->forKey('birthday', 'Input birthday')->required()->date();
        });
        $v->forKey('note')->optional()->string();

        return $this->buildResult($v);
    }
}
