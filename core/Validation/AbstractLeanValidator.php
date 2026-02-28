<?php

namespace WScore\Deca\Validation;

use WScore\Deca\Contracts\ValidatorInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use Wscore\LeanValidator\Sanitizer;
use Wscore\LeanValidator\Validator;

/**
 * AbstractLeanValidator is the base class for LeanValidator.
 * Please refer to the documentation of LeanValidator at vendor/wscore/leanvalidator.
 */
class AbstractLeanValidator implements ValidatorInterface
{
    protected Sanitizer $sanitizer;

    private ?ValidatorResultInterface $lastResult = null;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
    }

    /**
     * build the validator object for the data.
     * this method can be overridden by the subclass.
     * 
     * @override
     * @param array $data
     * @return Validator
     */
    protected function buildValidator(array $data): Validator
    {
        return Validator::make($data);
    }

    /**
     * validate the data using the validator object.
     * 
     * @override
     * @param array $data
     * @return ValidatorResultInterface
     */
    public function validate(array $data): ValidatorResultInterface
    {
        throw new \RuntimeException('validate() must be implemented.');
    }

    /**
     * Build result using LeanValidator API.
     * Uses getErrorsFlat() so errors are Aura-style flat array [field => message]
     * for compatibility with withInputs() and form templates (getError(name)).
     */
    protected function buildResult(Validator $validatorData): ValidatorResultInterface
    {
        if ($validatorData->isValid()) {
            $this->lastResult = new ValidatorSuccess($validatorData->getValidatedData());
            return $this->lastResult;
        }
        $this->lastResult = new ValidatorFailed($validatorData->getErrorsFlat());
        return $this->lastResult;
    }

    public function getErrors(): array
    {
        return $this->lastResult?->getErrors() ?? [];
    }

    public function success(): bool
    {
        return $this->lastResult?->success() ?? false;
    }

    public function failed(): bool
    {
        return $this->lastResult?->failed() ?? true;
    }
}