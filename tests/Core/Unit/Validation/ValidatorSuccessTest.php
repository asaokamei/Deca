<?php

namespace Tests\Core\Unit\Validation;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Validation\ValidatorSuccess;

class ValidatorSuccessTest extends TestCase
{
    public function testSuccessReturnsTrue(): void
    {
        $result = new ValidatorSuccess([], []);
        $this->assertTrue($result->success());
        $this->assertFalse($result->failed());
    }

    public function testGetValidatedData(): void
    {
        $raw = ['name' => '  John  ', 'age' => '30'];
        $validated = ['name' => 'John', 'age' => 30];
        $result = new ValidatorSuccess($raw, $validated);
        $this->assertEquals($validated, $result->getValidatedData());
    }

    public function testGetRawData(): void
    {
        $raw = ['name' => '  John  '];
        $result = new ValidatorSuccess($raw, ['name' => 'John']);
        $this->assertEquals($raw, $result->getRawData());
    }

    public function testGetErrorsThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no errors');

        $result = new ValidatorSuccess([], []);
        $result->getErrors();
    }
}
