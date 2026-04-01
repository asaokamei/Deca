<?php

namespace WScore\Deca\Contracts;

interface SessionInterface
{
    const POST_TOKEN_NAME = '_csrf_token';

    /**
     * validates the given token against the stored CSRF token.
     *
     * @param string $token
     * @return bool
     */
    public function validateCsRfToken(string $token): bool;

    /**
     * returns the current CSRF token, or generates one if it doesn't exist.
     *
     * @return string
     */
    public function getCsRfToken(): string;

    /**
     * returns the CSRF token name for use in HTML forms.
     *
     * @return string
     */
    public function getCsRfTokenName(): string;

    /**
     * regenerates the CSRF token for security.
     *
     * @return void
     */
    public function regenerateCsRfToken(): void;

    /**
     * gets flash data for the given key.
     * returns data set in the previous request or the current request.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getFlash(string $key, $default = null);

    /**
     * sets flash data for the given key to be available in the next request.
     * data is also available in the current request.
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function setFlash(string $key, $val);

    /**
     * clears flash data for the given key or all flash data if key is null.
     * affects both current and next request data.
     *
     * @param string|null $key
     * @return void
     */
    public function clearFlash(?string $key = null);

    /**
     * keeps flash data for the next request.
     * if key is provided, only that key is kept. otherwise, all current flash data is kept.
     *
     * @param string|null $key
     * @return void
     */
    public function keepFlash(?string $key = null);

    /**
     * saves data to the persistent session (not flash).
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function save(string $key, $val);

    /**
     * loads data from the persistent session (not flash).
     *
     * @param mixed $key
     * @return mixed
     */
    public function load($key);
}