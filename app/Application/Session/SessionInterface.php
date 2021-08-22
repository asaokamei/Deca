<?php

namespace App\Application\Session;

interface SessionInterface
{
    const POST_TOKEN_NAME = '_csrf_token';

    public function validateCsRfToken(string $token): bool;

    public function getCsRfToken(): string;

    public function regenerateCsRfToken(): void;

    public function getFlash(string $key, $default = null);

    public function setFlash(string $key, $val);

    public function clearFlash();

    public function save(string $key, $val);

    public function load($key);
}