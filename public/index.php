<?php
declare(strict_types=1);

use Slim\App;
use WScore\Deca\Services\Setting;

if (php_sapi_name() == 'cli-server') {
    /* 静的コンテンツのルーティングをして false を返します */
    $path = $_SERVER["REQUEST_URI"];
    if (is_dir($path)) goto SERVER;
    if (file_exists($path)) return false;
    if ($path === '/worker.js') return false;
}
SERVER:

require_once dirname(__DIR__) . '/vendor/autoload.php';

$setting = Setting::forge(__DIR__ . '/../settings.ini', $_ENV);
require __DIR__ . '/../appDemo/getContainer.php';
require __DIR__ . '/../appDemo/getApp.php';
require __DIR__ . '/../appDemo/routes.php';

/** @var App $app */
$app->run();