<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use WScore\Deca\Interfaces\ViewInterface;

if (!isset($app)) {
    return;
}
if (!$app instanceof App){
    return;
}

/**
 * set up main routes
 */
$app->get('/', function (Request $request, Response $response) {
    return $this->get(ViewInterface::class)->render($response, 'hello.twig', [
        'app_name' => $_ENV['APP_NAME'] ?? 'no-app-name-is-set!',
    ]);
})->setName('hello');