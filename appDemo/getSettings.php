<?php
declare(strict_types=1);

use WScore\Deca\Services\Setting;

if (!function_exists('getSettings')) {
    /**
     * Load application settings from an ini file (merged with $_ENV).
     */
    function getSettings(string $settingsIniPath): Setting
    {
        return Setting::forge($settingsIniPath);
    }
}
