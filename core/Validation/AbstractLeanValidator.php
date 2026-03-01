<?php

namespace WScore\Deca\Validation;

use WScore\Deca\Contracts\ValidatorInterface;
use WScore\Deca\Contracts\ValidatorResultInterface;
use Wscore\LeanValidator\Sanitizer;
use Wscore\LeanValidator\Validator;

/**
 * Base class for Deca validators backed by **Wscore\LeanValidator**.
 * (Deca may also provide other base validators using different libraries.)
 *
 * IMPORTANT:
 * - Subclasses MUST implement validate() using LeanValidator APIs (Validator/Sanitizer).
 * - Do NOT invent validation methods or write Laravel/Symfony-style validation here.
 * - If an API is unclear, check vendor/wscore/leanvalidator (README/source) first.
 *
 * Recommended flow: sanitize -> clean($data) -> buildValidator($cleanedData) -> rules -> buildResult($v)
 * Error contract: buildResult() uses getErrorsFlat() => [field => message] (dot-notation for nested fields).
 *
 * @see \AppDemo\Application\Forms\SampleLeanValidator
 */
class AbstractLeanValidator implements ValidatorInterface
{
    protected Sanitizer $sanitizer;

    private ?ValidatorResultInterface $lastResult = null;
    protected array $rawData;

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
     * Implement using Wscore\LeanValidator API (Validator/Sanitizer).
     * * Follow: sanitize -> buildValidator -> rules -> buildResult.
     * 
     * @override
     * @param array $data
     * @return ValidatorResultInterface
     */
    public function validate(array $data): ValidatorResultInterface
    {
        $this->rawData = $data;
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
            $this->lastResult = new ValidatorSuccess($this->rawData, $validatorData->getValidatedData());
            return $this->lastResult;
        }
        $this->lastResult = new ValidatorFailed($this->rawData, $validatorData->getErrorsFlat());
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