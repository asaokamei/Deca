<?php

namespace Tests\Core\Unit\Views;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Views\FormDotted;

class FormDottedTest extends TestCase
{
    public function testGetByPathExactKey(): void
    {
        $errors = new FormDotted(['name' => 'Name is Required', 'profile.email' => 'Input valid email']);
        $this->assertEquals('Name is Required', $errors->getByPath('name'));
        $this->assertEquals('Input valid email', $errors->getByPath('profile.email'));
        $this->assertNull($errors->getByPath('missing'));
    }

    public function testGetByNameConvertsBracketToDot(): void
    {
        $errors = new FormDotted(['profile.email' => 'Input valid email', 'dev.framework' => 'Select Framework']);
        $this->assertEquals('Input valid email', $errors->getByName('profile[email]'));
        $this->assertEquals('Select Framework', $errors->getByName('dev[framework]'));
    }

    public function testGetByPathCollectsArrayErrors(): void
    {
        $errors = new FormDotted(['dev.ai.0' => 'Invalid', 'dev.ai.1' => 'Invalid']);
        $got = $errors->getByPath('dev.ai');
        $this->assertIsArray($got);
        $this->assertEqualsCanonicalizing(['Invalid', 'Invalid'], $got);
    }

    public function testGetByNameArrayInput(): void
    {
        $errors = new FormDotted(['dev.ai.0' => 'Invalid']);
        $this->assertEquals('Invalid', $errors->getByName('dev[ai][]'));
    }

    public function testCheckIf(): void
    {
        $errors = new FormDotted(['lang' => 'en']);
        $this->assertTrue($errors->checkIf('lang', 'en'));
        $this->assertFalse($errors->checkIf('lang', 'ja'));
    }

    public function testGetByPathEmptyStringReturnsAllData(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $form = new FormDotted($data);
        $this->assertEquals($data, $form->getByPath(''));
    }
}
