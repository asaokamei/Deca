<?php

namespace Tests\Core\Unit\Services;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Services\Setting;

class SettingTest extends TestCase
{
    public function testGetAndHas(): void
    {
        $setting = new Setting(['test' => 'tested']);
        $this->assertTrue($setting->has('test'));
        $this->assertFalse($setting->has('none'));
        $this->assertEquals('tested', $setting->get('test'));
        $this->assertEquals('tested', $setting->test);
        $this->assertEquals('tested', $setting['test']);
    }

    public function testAppEnv(): void
    {
        $setting = new Setting(['APP_ENV' => 'development']);
        $this->assertEquals('development', $setting->appEnv());
    }

    public function testAppEnvDefaultProductionWhenMissing(): void
    {
        $setting = new Setting([]);
        $this->assertEquals('production', $setting->appEnv());
    }

    public function testAppEnvNormalizesToLowercase(): void
    {
        $setting = new Setting(['APP_ENV' => 'DEVELOPMENT']);
        $this->assertEquals('development', $setting->appEnv());
    }

    public function testIsProduction(): void
    {
        $this->assertTrue((new Setting(['APP_ENV' => 'production']))->isProduction());
        $this->assertTrue((new Setting(['APP_ENV' => 'prod']))->isProduction());
        $this->assertFalse((new Setting(['APP_ENV' => 'development']))->isProduction());
    }

    public function testIsDebug(): void
    {
        $this->assertTrue((new Setting(['APP_DEBUG' => true]))->isDebug());
        $this->assertTrue((new Setting(['APP_DEBUG' => '1']))->isDebug());
        $this->assertFalse((new Setting(['APP_DEBUG' => false]))->isDebug());
        $this->assertFalse((new Setting([]))->isDebug());
    }

    public function testAddSettings(): void
    {
        $setting = new Setting(['a' => 1]);
        $setting->addSettings(['b' => 2]);
        $this->assertEquals(1, $setting->get('a'));
        $this->assertEquals(2, $setting->get('b'));
    }

    public function testOffsetExistsAndGet(): void
    {
        $setting = new Setting(['key' => 'value']);
        $this->assertTrue(isset($setting['key']));
        $this->assertEquals('value', $setting['key']);
    }

    public function testOffsetSetThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot set');
        $setting = new Setting([]);
        $setting['x'] = 'y';
    }

    public function testOffsetUnsetThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot unset');
        $setting = new Setting(['x' => 'y']);
        unset($setting['x']);
    }

    public function testGetIterator(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $setting = new Setting($data);
        $this->assertEquals($data, iterator_to_array($setting));
    }

    public function testForgeLoadsIniAndMergesEnv(): void
    {
        $iniPath = __DIR__ . '/../../../Fixtures/settings.test.ini';
        $this->assertFileExists($iniPath);

        $setting = Setting::forge($iniPath, ['EXTRA' => 'from-env']);
        $this->assertTrue($setting->has('APP_ENV'));
        $this->assertEquals('test', $setting->get('APP_ENV'));
        $this->assertEquals('deca-test', $setting->get('APP_NAME'));
        $this->assertEquals('from-env', $setting->get('EXTRA'));
    }

    public function testForgeEnvOverridesIniForSameKey(): void
    {
        $iniPath = __DIR__ . '/../../../Fixtures/settings.test.ini';
        $setting = Setting::forge($iniPath, [
            'APP_ENV' => 'production',
            'APP_NAME' => 'from-env-only',
        ]);
        $this->assertEquals('production', $setting->get('APP_ENV'));
        $this->assertEquals('from-env-only', $setting->get('APP_NAME'));
    }

    public function testForgeThrowsWhenFileMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find a setting file');

        Setting::forge(__DIR__ . '/nonexistent.ini');
    }
}
