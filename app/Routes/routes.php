<?php
declare(strict_types=1);

use App\Application\Interfaces\ViewInterface;
use App\Routes\Actions\FormSample\FormController;
use App\Routes\Actions\WelcomeSample\WelcomeAction;
use App\Routes\Controllers\Samples\CsRfController;
use App\Routes\Controllers\Samples\ErrorController;
use App\Routes\Controllers\Samples\FlashController;
use App\Routes\Controllers\Samples\MailController;
use App\Routes\Controllers\Samples\ResourceController;
use App\Routes\Controllers\Samples\WelcomeController;
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
    return $this->get(ViewInterface::class)->render($response, 'hello.twig', [
        'app_name' => $_ENV['APP_NAME'] ?? 'no-app-name-is-set!',
    ]);
})->setName('hello');
$app->get('/info', function (Request $request, Response $response) {
    phpinfo();
    exit;
})->setName('phpinfo');

/**
 * sample groups. 
 */
$app->group('/samples', function (Group $group) {
    $group->any('/form', FormController::class)->setName('form');
    $group->any('/welcome/{name:.*}', WelcomeAction::class)->setName('welcome');
    $group->any('/flashes/[{method}]', FlashController::class)->setName('flashes');
    $group->any('/resource/[{action}[/{id}]]', ResourceController::class)->setName('resource');
    $group->any('/mail', MailController::class)->setName('mail');
});

$app->group('/errors', function(Group $group) {
    /** @noinspection PhpUndefinedClassInspection  this is an example route for calling non-existent controller */
    $group->get('/nonExist', NonExistController::class)->setName('nonExists');
    $group->any('/csrf', CsRfController::class)->setName('csrf');
    $group->any('/div0', ErrorController::class)->setName('div0');
});