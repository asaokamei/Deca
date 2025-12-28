<?php

namespace WScore\Deca\Validation;

use Aura\Filter\FilterFactory;
use Aura\Filter\Spec\ValidateSpec;
use Aura\Filter\SubjectFilter;
use RuntimeException;
use WScore\Deca\Interfaces\ValidatorInterface;
use WScore\Deca\Validation\Rules\ArrayCallable;
use WScore\Deca\Validation\Rules\ArrayValues;

class AbstractAuraValidator implements ValidatorInterface
{
    protected FilterFactory $factory;
    protected SubjectFilter $filter;
    private array $fields = [];
    private bool $success;
    private array $validatedData;

    public function __construct()
    {
        $this->factory = new FilterFactory(
            $this->makeValidators()
        );
        $this->buildRules();
    }

    /**
     * @Override
     */
    protected function makeValidators(): array
    {
        return [
            'arrayValues' => function() {
                return new ArrayValues();
            },
            'arrayCallable' => function() {
                return new ArrayCallable();
            }
        ];
    }

    protected function field(string $key, $createSpec = true): ?ValidateSpec
    {
        $this->fields[$key] = $key;
        if (!$createSpec) {
            return null;
        }
        return $this->filter->validate($key);
    }

    /**
     * @Override
     */
    protected function buildRules(?array $data = null): void
    {
        $this->filter = $this->factory->newSubjectFilter();
        throw new RuntimeException('buildRules() must be implemented.');
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