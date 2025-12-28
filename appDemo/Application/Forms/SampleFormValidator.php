<?php

namespace AppDemo\Application\Forms;

use Aura\Filter\FilterFactory;
use Aura\Filter\Spec\ValidateSpec;
use Aura\Filter\SubjectFilter;
use WScore\Deca\Interfaces\ValidatorInterface;

class SampleFormValidator implements ValidatorInterface
{
    private SubjectFilter $filter;
    private array $fields = [];
    private bool $success;
    private array $validatedData;

    public function __construct()
    {
        $factory = new FilterFactory(
            $this->makeValidators()
        );
        $this->filter = $factory->newSubjectFilter();
        $this->buildRules();
    }

    private function makeValidators(): array
    {
        return [
            'arrayValues' => function() {
                return function($subject, $field, $list) {
                    $value = $subject->$field;
                    if (!is_array($value)) {
                        return false;
                    }
                    foreach ($value as $item) {
                        if (!in_array($item, $list)) {
                            return false;
                        }
                    }
                    return true;
                };
            }
        ];
    }
    private function field(string $key, $createSpec = true): ?ValidateSpec
    {
        if (str_contains($key, '.')) {
            $key = substr($key, 0, strrpos($key, '.'));
        }
        if (!isset($this->fields[$key])) {
            $this->fields[$key] = $key;
        }
        if (!$createSpec) {
            return null;
        }
        return $this->filter->validate($key);
    }

    protected function buildRules(?array $data = null): void
    {
        if ($data !== null) {
            return;
        }
        $this->field('name')
            ->isNotBlank()
            ->setMessage('必須項目です');

        $this->field('say')
            ->isBlankOr('equalToValue', 'yeah')
            ->setMessage('Say Yeah! if you like');

        $this->field('language')
            ->isBlankOr('inValues', ['en', 'ja'])
            ->setMessage('言語を選択してください');

        $this->field('framework')
            ->isNotBlank()
            ->is('inValues', ['LARAVEL', 'SYMFONY', 'SLIM'])
            ->setMessage('フレームワークを選択してください');

        $this->field('ai')
            ->isNotBlank()
            ->is('arrayValues', ['CHATGPT', 'GEMINI', 'CLAUDE'])
            ->setMessage('AIを選択してください');

        $this->field('email')
            ->is('email')
            ->isNotBlank()
            ->setMessage('有効なメールアドレスを入力してください');

        $this->field('birthday')
            ->is('dateTime')
            ->isNotBlank()
            ->setMessage('有効な日付を入力してください');

        $this->field('note', false);
    }

    public function validate(array $data): bool
    {
        $this->buildRules($data);
        $this->success = $this->filter->apply($data);
        $this->validatedData = $data;

        return $this->success;
    }

    public function failed(): bool
    {
        return !$this->success;
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function getErrors(): array
    {
        $failed = $this->filter->getFailures();
        return $failed->getMessages();
    }

    public function getValidData(): array
    {
        $valid = [];
        foreach ($this->fields as $field) {
            $valid[$field] = $this->validatedData[$field] ?? null;
        }
        return $valid;
    }
}