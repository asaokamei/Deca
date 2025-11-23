<?php
declare(strict_types=1);

namespace App\Application\Container;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 *
 * @property string $projectRoot
 * @property string $cacheDirectory
 */
class Setting implements ArrayAccess, IteratorAggregate
{
    private const APP_ENV = 'APP_ENV';
    private const APP_DEBUG = 'APP_DEBUG';
    private const PRODUCTION = 'production';

    private array $prodStrings = [self::PRODUCTION, 'prod'];

    private array $settings;

    private array $env;

    public function __construct(array $settings, array $env = [])
    {
        $this->settings = $settings;
        $this->env = $env;
    }

    public static function forge(string $settingFile, array $env = null): Setting
    {
        if (!file_exists($settingFile)) {
            throw new RuntimeException('Cannot find a setting file: ' . $settingFile);
        }
        if ($env === null) {
            $env = $_ENV;
        }
        $settings = parse_ini_file($settingFile);
        if (is_array($settings)) {
            return new self($settings, $env);
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
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->env)) {
            return $this->env[$key];
        }
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }
        return null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
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
        return in_array($this->getEnv(), $this->prodStrings);
    }

    public function isDebug(): bool
    {
        return (bool) ($this->get(self::APP_DEBUG) ?? false);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Cannot set an offset!');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Cannot unset an offset!');
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->settings);
    }
}