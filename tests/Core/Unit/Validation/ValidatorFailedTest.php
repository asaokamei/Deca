<?php

namespace Tests\Core\Unit\Validation;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Validation\ValidatorFailed;

class ValidatorFailedTest extends TestCase
{
    public function testFailedReturnsTrue(): void
    {
        $result = new ValidatorFailed([], []);
        $this->assertTrue($result->failed());
        $this->assertFalse($result->success());
    }

    public function testGetErrors(): void
    {
        $errors = ['name' => 'Name is required', 'email' => 'Invalid email'];
        $result = new ValidatorFailed(['name' => '', 'email' => 'x'], $errors);
        $this->assertEquals($errors, $result->getErrors());
    }

    public function testGetRawData(): void
    {
        $raw = ['name' => '', 'email' => 'x'];
        $result = new ValidatorFailed($raw, ['name' => 'Required']);
        $this->assertEquals($raw, $result->getRawData());
    }

    public function testGetValidatedDataThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no valid data');

        $result = new ValidatorFailed([], ['field' => 'error']);
        $result->getValidatedData();
    }
}
