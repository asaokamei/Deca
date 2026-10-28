<?php
declare(strict_types=1);

namespace Tests\Demo\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use WScore\Deca\Adapters\Session\Session;

abstract class HttpTestCase extends TestCase
{
    protected array $sessionData = [];

    protected function setUp(): void
    {
        $this->sessionData = [];
    }

    protected function resolveSettingsPath(): string
    {
        $settingsIniPath = __DIR__ . '/../../../settings.ini';
        if (!is_file($settingsIniPath)) {
            return __DIR__ . '/../../../settings.demo.ini';
        }

        return $settingsIniPath;
    }

    protected function createRequest(string $method, string $path, array $postData = []): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        $uri = $factory->createUri($path);
        $request = $factory->createServerRequest($method, $uri);

        if ($method === 'POST') {
            $request = $request->withParsedBody($postData);
        }

        return $request;
    }

    protected function createApp(bool $appDebug = false): App
    {
        require_once __DIR__ . '/../../../appDemo/boot.php';

        $setting = getSettings($this->resolveSettingsPath());
        $setting->addSettings(['APP_DEBUG' => $appDebug]);

        $definitions = getDefinitions($setting);
        $definitions->setValue(Session::class, new Session($this->sessionData));
        $container = getContainer($definitions);

        $app = getApp($container);
        registerRoutes($app);

        return $app;
    }
}
