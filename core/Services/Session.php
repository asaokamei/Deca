<?php

namespace WScore\Deca\Services;

use WScore\Deca\Contracts\SessionInterface;

class Session implements SessionInterface
{
    private array $data;
    private string $csrf_token_name = self::POST_TOKEN_NAME;
    private bool $aged = false;

    private const SESSION_KEY = 'wscore_deca_session';
    private const FLASH_NOW = '_flash_now';
    private const FLASH_NEXT = '_flash_next';
    private const CSRF_TOKEN = '_csrf_token';

    /**
     * @param array|null $data
     */
    public function __construct(?array &$data = null)
    {
        if ($data === null) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (!isset($_SESSION[self::SESSION_KEY])) {
                $_SESSION[self::SESSION_KEY] = [
                    self::FLASH_NOW => [],
                    self::FLASH_NEXT => [],
                ];
            }
            $this->data = &$_SESSION[self::SESSION_KEY];
        } else {
            $this->data = &$data;
            if (!isset($this->data[self::FLASH_NOW])) {
                $this->data[self::FLASH_NOW] = [];
            }
            if (!isset($this->data[self::FLASH_NEXT])) {
                $this->data[self::FLASH_NEXT] = [];
            }
        }
        $this->ageFlash();
    }

    private function ageFlash(): void
    {
        if ($this->aged) {
            return;
        }
        $this->data[self::FLASH_NOW] = $this->data[self::FLASH_NEXT];
        $this->data[self::FLASH_NEXT] = [];
        $this->aged = true;
    }

    public function validateCsRfToken(string $token): bool
    {
        $saved = $this->getCsRfToken();
        return !empty($token) && hash_equals($saved, $token);
    }

    public function getCsRfToken(): string
    {
        if (!isset($this->data[self::CSRF_TOKEN])) {
            $this->regenerateCsRfToken();
        }
        return $this->data[self::CSRF_TOKEN];
    }

    public function getCsRfTokenName(): string
    {
        return $this->csrf_token_name;
    }

    public function regenerateCsRfToken(): void
    {
        $this->data[self::CSRF_TOKEN] = bin2hex(random_bytes(32));
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $this->data[self::FLASH_NEXT][$key] ?? $this->data[self::FLASH_NOW][$key] ?? $default;
    }

    public function setFlash(string $key, mixed $val): void
    {
        $this->data[self::FLASH_NEXT][$key] = $val;
    }

    public function clearFlash(?string $key = null): void
    {
        if ($key === null) {
            $this->data[self::FLASH_NOW] = [];
        } else {
            unset($this->data[self::FLASH_NOW][$key]);
            unset($this->data[self::FLASH_NEXT][$key]);
        }
    }

    public function keepFlash(?string $key = null): void
    {
        if ($key === null) {
            $this->data[self::FLASH_NEXT] = array_merge($this->data[self::FLASH_NEXT], $this->data[self::FLASH_NOW]);
        } elseif (isset($this->data[self::FLASH_NOW][$key])) {
            $this->data[self::FLASH_NEXT][$key] = $this->data[self::FLASH_NOW][$key];
        }
    }

    public function save(string $key, mixed $val): void
    {
        $this->data[$key] = $val;
    }

    public function load($key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $csrf_token_name
     * @return $this
     */
    public function setCsrfTokenName(string $csrf_token_name): self
    {
        $this->csrf_token_name = $csrf_token_name;
        return $this;
    }
}