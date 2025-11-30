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
    $view = $this->get(ViewInterface::class);
    $view->setRequest($request);
    return $view->render($response, 'hello.twig', [
        'app_name' => $_ENV['APP_NAME'] ?? 'APP_NAME is blank!',
    ]);
})->setName('hello');

$app->get('/info', function () {
    phpinfo();
    exit;
});