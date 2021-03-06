<?php
declare(strict_types=1);

use App\AppBuilder;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\ResponseEmitter;

if (php_sapi_name() == 'cli-server') {
    /* 静的コンテンツのルーティングをして false を返します */
    $path = $_SERVER["REQUEST_URI"];
    if (is_dir($path)) goto SERVER;
    if (file_exists($path)) return false;
    if ($path === '/worker.js') return false;
}
SERVER:

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/error.php';

// Create Request object from globals
$request = ServerRequestCreatorFactory::create()
    ->createServerRequestFromGlobals();

$app = AppBuilder::forge(dirname(__DIR__))
    ->loadSettings()
    ->loadContainer(false)
    ->build($request);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
