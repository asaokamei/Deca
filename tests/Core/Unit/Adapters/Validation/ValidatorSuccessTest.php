<?php

namespace Tests\Core\Unit\Adapters\Validation;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WScore\Deca\Adapters\Validation\ValidatorSuccess;

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
        $this->assertEquals($raw, $result->getRawDataBag()->getData());
    }

    public function testGetErrorsThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no errors');

        $result = new ValidatorSuccess([], []);
        $result->getErrorBag();
    }
}
