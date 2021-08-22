<?php
declare(strict_types=1);

use App\Controllers\Samples\CsRfController;
use App\Controllers\Samples\FlashController;
use App\Controllers\Samples\FormController;
use App\Controllers\Samples\WelcomeController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

if (!isset($app)) {
    return;
}
if (!$app instanceof App){
    return;
}

/**
 * set up routes
 */
$app->get('/', function (Request $request, Response $response) {
    return $this->get(\App\Application\Interfaces\ViewInterface::class)->render($response, 'hello.twig', [
        'app_name' => $_ENV['APP_NAME'] ?? 'no-app-name-is-set!',
    ]);
})->setName('hello');

/**
 * sample groups. 
 */
$app->group('/samples', function (Group $group) {
    $group->any('/form', FormController::class)->setName('form');
    $group->any('/csrf', CsRfController::class)->setName('csrf');
    $group->any('/welcome/{name:.*}', WelcomeController::class)->setName('welcome');
    $group->any('/flashes/[{method}]', FlashController::class)->setName('flashes');
    /** @noinspection PhpUndefinedClassInspection  this is an example route for calling non-existent controller */
    $group->get('/nonExist', NonExistController::class)->setName('nonExists');
});
