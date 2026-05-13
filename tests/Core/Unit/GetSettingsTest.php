<?php

namespace Tests\Core\Unit;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Services\Setting;

require_once __DIR__ . '/../../../appDemo/getSettings.php';

class GetSettingsTest extends TestCase
{
    private string $originalTimeZone;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalTimeZone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $tempFile) {
            @unlink($tempFile);
        }
        date_default_timezone_set($this->originalTimeZone);
        parent::tearDown();
    }

    public function testGetSettingsSetsTimeZoneWhenConfigured(): void
    {
        $iniPath = $this->createIniFile(
            "[Application]\n"
            . "APP_ENV = test\n"
            . "TIME_ZONE = Europe/Paris\n"
        );

        $setting = getSettings($iniPath);

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertSame('Europe/Paris', date_default_timezone_get());
    }

    public function testGetSettingsDoesNotSetTimeZoneWhenMissing(): void
    {
        date_default_timezone_set('UTC');
        $iniPath = $this->createIniFile(
            "[Application]\n"
            . "APP_ENV = test\n"
        );

        getSettings($iniPath);

        $this->assertSame('UTC', date_default_timezone_get());
    }

    public function testGetSettingsDoesNotSetTimeZoneWhenEmpty(): void
    {
        date_default_timezone_set('UTC');
        $iniPath = $this->createIniFile(
            "[Application]\n"
            . "APP_ENV = test\n"
            . "TIME_ZONE = \n"
        );

        getSettings($iniPath);

        $this->assertSame('UTC', date_default_timezone_get());
    }

    private function createIniFile(string $content): string
    {
        $iniPath = tempnam(sys_get_temp_dir(), 'deca-settings-');
        if ($iniPath === false) {
            $this->fail('Failed to create temp ini file.');
        }

        file_put_contents($iniPath, $content);
        $this->assertFileExists($iniPath);
        $this->tempFiles[] = $iniPath;

        return $iniPath;
    }
}
