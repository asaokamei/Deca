<?php
declare(strict_types=1);

use WScore\Deca\Services\Setting;

if (!function_exists('getSettings')) {
    /**
     * Load application settings from an ini file (merged with $_ENV).
     */
    function getSettings(string $settingsIniPath): Setting
    {
        $setting = Setting::forge($settingsIniPath);

        $timeZone = $setting->get('TIME_ZONE');
        if (is_string($timeZone) && $timeZone !== '') {
            date_default_timezone_set($timeZone);
        }

        return $setting;
    }
}
