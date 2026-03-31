<?php

namespace Tests\Core\Unit\Validation;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Contracts\ValidatorResultInterface;
use WScore\Deca\Validation\AbstractLeanValidator;
use Wscore\LeanValidator\Validator;

class AbstractLeanValidatorTest extends TestCase
{
    /**
     * @return AbstractLeanValidator
     */
    private function createValidator(): AbstractLeanValidator
    {
        return new class extends AbstractLeanValidator {
            protected function validation(array $data): ValidatorResultInterface
            {
                // 1. sanitize (in actual use, you would set this in constructor or another method)
                $this->sanitizer->toTrim('name');
                
                // 2. clean
                $cleaned = $this->sanitizer->clean($data);
                
                // 3. buildValidator
                $v = $this->buildValidator($cleaned);
                
                // 4. rules
                $v->forKey('name')->required()->string();
                $v->forKey('age')->required()->int();
                
                // 5. buildResult
                return $this->buildResult($v);
            }
        };
    }

    public function testSuccess(): void
    {
        $validator = $this->createValidator();
        $data = ['name' => '  John  ', 'age' => 30]; // Changed '30' to 30 because int() rule requires integer type
        $result = $validator->validate($data);

        $this->assertTrue($result->success());
        $this->assertFalse($result->failed());
        $this->assertEquals(['name' => 'John', 'age' => 30], $result->getValidatedData());
        $this->assertEquals($data, $result->getRawDataBag()->getData());
    }

    public function testFailed(): void
    {
        $validator = $this->createValidator();
        $data = ['name' => '  ', 'age' => '30']; // age is string, int() will fail
        $result = $validator->validate($data);

        $this->assertFalse($result->success());
        $this->assertTrue($result->failed());
        $this->assertArrayHasKey('name', $result->getErrorBag()->getData());
        $this->assertArrayHasKey('age', $result->getErrorBag()->getData());
        $this->assertEquals($data, $result->getRawDataBag()->getData());
    }

    public function testUnimplementedValidationThrowsException(): void
    {
        $validator = new class extends AbstractLeanValidator {};
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('validation() must be implemented.');
        $validator->validate(['test' => 'data']);
    }

    public function testBuildValidator(): void
    {
        $validator = new class extends AbstractLeanValidator {
            public function callBuildValidator(array $data): Validator
            {
                return $this->buildValidator($data);
            }
        };
        $data = ['foo' => 'bar'];
        $v = $validator->callBuildValidator($data);
        $this->assertInstanceOf(Validator::class, $v);
        // Use getData() to get the raw input data
        $this->assertEquals($data, $v->getData());
    }
}
