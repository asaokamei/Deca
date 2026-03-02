<?php

namespace Tests\Core\Unit\Views;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Views\FormData;

class FormDataTest extends TestCase
{
    public function testOldValues(): void
    {
        $oldValues = new FormData(['name' => 'John', 'age' => 30]);
        $this->assertEquals('John', $oldValues->getByPath('name'));
        $this->assertEquals(30, $oldValues->getByPath('age'));
        $this->assertNull($oldValues->getByPath('address'));
    }

    public function testGetValueByPath(): void
    {
        $oldValues = new FormData(['inputs' => ['name' => 'John', 'lang' => ['ENG', 'JPN']]]);
        $this->assertEquals('John', $oldValues->getByPath('inputs.name'));
        $this->assertEquals('ENG', $oldValues->getByPath('inputs.lang.0'));
        $this->assertEquals(['ENG', 'JPN'], $oldValues->getByPath('inputs.lang'));
    }

    public function testGetValueByName(): void
    {
        $oldValues = new FormData(['inputs' => ['name' => 'John', 'lang' => ['ENG', 'JPN']]]);
        $this->assertEquals('John', $oldValues->getByName('inputs[name]'));
        $this->assertEquals('ENG', $oldValues->getByName('inputs[lang][0]'));
        $this->assertEquals(['ENG', 'JPN'], $oldValues->getByName('inputs[lang]'));
    }

    public function testGetValueForObject(): void
    {
        $entity = new \stdClass();
        $entity->name = 'John';
        $entity->lang = ['ENG', 'JPN'];
        $oldValues = new FormData(['inputs' => $entity]);
        $this->assertEquals('John', $oldValues->getByName('inputs[name]'));
        $this->assertEquals('ENG', $oldValues->getByName('inputs[lang][0]'));
    }

    public function testGetValueForObjectGetter(): void
    {
        $entity = new class {
            public function name() { return 'John'; }
            public function getLang() { return ['ENG', 'JPN']; }
            public function get($name) { return $name . ' getter'; }
        };
        $oldValues = new FormData(['inputs' => $entity]);
        $this->assertEquals('John', $oldValues->getByName('inputs[name]'));
        $this->assertEquals('ENG', $oldValues->getByName('inputs[lang][0]'));
        $this->assertEquals('anything getter', $oldValues->getByName('inputs[anything]'));
    }

    public function testCheckIf(): void
    {
        $oldValues = new FormData(['inputs' => ['name' => 'John', 'lang' => ['ENG', 'JPN']]]);
        $this->assertTrue($oldValues->checkIf('inputs[name]', 'John'));
        $this->assertTrue($oldValues->checkIf('inputs[lang]', 'ENG'));
        $this->assertTrue($oldValues->checkIf('inputs[lang]', 'JPN'));
        $this->assertFalse($oldValues->checkIf('inputs[lang]', 'ESP'));
    }
}
