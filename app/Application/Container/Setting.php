<?php

namespace App\Application\Container;

use ArrayAccess;
use IteratorAggregate;
use RuntimeException;

class Setting implements ArrayAccess, IteratorAggregate
{
    const APP_ENV = 'APP_ENV';
    const PRODUCTION = 'production';

    /**
     * @var array|false
     */
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public static function forge(string $settingFile): Setting
    {
        if (!file_exists($settingFile)) {
            return new self([]);
        }
        $settings = parse_ini_file($settingFile);
        if (is_array($settings)) {
            return new self($settings);
        }
        throw new RuntimeException('Failed to parse a setting file: ' . $settingFile);
    }

    public function addSettings(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * @param string $key
     * @return string|string[]|null
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @return string[]|string|null
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        return null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    public function getSettings(string $key): Setting
    {
        $setting = $this->get($key);
        if (is_array($setting)) {
            return new self($setting);
        }
        if ($setting === null) {
            return new self([]);
        }
        return new self([$setting]);
    }

    public function getEnv(): string
    {
        $env = $this->get(self::APP_ENV);
        if (is_string($env)) {
            return $env ?? self::PRODUCTION;
        }
        if ($env === null) {
            return self::PRODUCTION;
        }
        throw new RuntimeException('Environment is not a string.');
    }

    public function isProduction(): bool
    {
        return $this->getEnv() === self::PRODUCTION;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Cannot set an offset!');
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException('Cannot unset an offset!');
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->settings);
    }
}