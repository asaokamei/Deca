<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI !== 'cli-server' && session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

ob_start();