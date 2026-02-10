<?php
declare(strict_types=1);

use WScore\Deca\Services\Setting;

if (php_sapi_name() == 'cli-server') {
    /* 静的コンテンツのルーティングをして false を返します */
    $path = $_SERVER["REQUEST_URI"];
    if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $path)) {
        return false;    // リクエストされたリソースをそのままの形式で扱います。
    }
}
SERVER:

require_once dirname(__DIR__) . '/vendor/autoload.php';

require __DIR__ . '/../appDemo/boot.php';

$setting = Setting::forge(__DIR__ . '/../settings.ini', $_ENV);
$container = getContainer($setting);
$app = getApp($container);
setRoutes($app);

// All OK!
$app->run();