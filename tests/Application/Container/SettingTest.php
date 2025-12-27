<?php

namespace Tests\Application\Container;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Services\Setting;

class SettingTest extends TestCase
{
    public function testGet()
    {
        $setting = new Setting(['test' => 'tested']);
        $this->assertTrue($setting->has('test'));
        $this->assertFalse($setting->has('none'));
        $this->assertEquals('tested', $setting->get('test'));
        $this->assertEquals('tested', $setting->test);
        $this->assertEquals('tested', $setting['test']);
    }
}
