<?php
declare(strict_types=1);

if (php_sapi_name() == 'cli-server') {
    /* 静的コンテンツのルーティングをして false を返します */
    $path = $_SERVER["REQUEST_URI"];
    if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $path)) {
        return false;    // リクエストされたリソースをそのままの形式で扱います。
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../appDemo/boot.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$settingsIniPath = dirname(__DIR__) . '/settings.ini';
$setting = getSettings($settingsIniPath);
$definitions = getDefinitions($setting);
$container = getContainer($definitions);
$app = getApp($container);
registerRoutes($app);

$app->run();