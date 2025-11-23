<?php
declare(strict_types=1);

if (php_sapi_name() == 'cli-server') {
    /* 静的コンテンツのルーティングをして false を返します */
    $path = $_SERVER["REQUEST_URI"];
    if (is_dir($path)) goto SERVER;
    if (file_exists($path)) return false;
    if ($path === '/worker.js') return false;
}
SERVER:

require_once dirname(__DIR__) . '/vendor/autoload.php';

require __DIR__ . '/getContainer.php';
require __DIR__ . '/getApp.php';
require __DIR__ . '/routes.php';

/** @var \Slim\App $app */
$app->run();