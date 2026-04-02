<?php
declare(strict_types=1);

namespace Tests\Http\Demo;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use WScore\Deca\Views\Twig\ViewTwig;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Services\Routing;
use Nyholm\Psr7\Factory\Psr17Factory;
use WScore\Deca\Services\Setting;
use WScore\Deca\Services\Session;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Definitions;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Log\LoggerInterface;
use WScore\Deca\Handlers\SimpleErrorHandler;
use WScore\Deca\Middleware\AppMiddleware;
use Slim\Interfaces\RouteCollectorInterface;
use DI\Container;

class FlashControllerTest extends TestCase
{
    private array $sessionData = [];

    protected function setUp(): void
    {
        $this->sessionData = [];
    }

    private function createRequest(string $method, string $path): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        $uri = $factory->createUri($path);
        // getApp.phpで addRoutingMiddleware() が呼ばれるので、
        // テスト環境でベースパスがある場合は調整が必要かもしれないが、
        // ここでは単純なリクエストを作成する。
        return $factory->createServerRequest($method, $uri);
    }

    private function createApp(): App
    {
        require_once __DIR__ . '/../../../appDemo/boot.php';

        $definitions = new Definitions();
        $session = new Session($this->sessionData);
        $definitions->setValue(Session::class, $session);

        $container = getContainer(null, $definitions);
        $app = getApp($container);
        setRoutes($app);

        return $app;
    }

    public function test_onGet_displays_flash_page()
    {
        $app = $this->createApp();
        $request = $this->createRequest('GET', '/samples/flashes/get');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('Flash Messages', $html);
    }

    public function test_onPage_sets_messages_and_displays_them()
    {
        $app = $this->createApp();
        $request = $this->createRequest('GET', '/samples/flashes/page');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        
        // onPage内で設定されたメッセージが表示されているか
        $this->assertStringContainsString('This notice is set in onPage method.', $html);
        $this->assertStringContainsString('This message is set in onPage method.', $html);
    }

    public function test_onBack_redirects_and_preserves_messages()
    {
        // 1. backを呼び出す
        $app = $this->createApp();
        $request = $this->createRequest('GET', '/samples/flashes/back');
        $response = $app->handle($request);

        // リダイレクトの確認
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringContainsString('/samples/flashes/', $response->getHeaderLine('Location'));

        // セッションにメッセージが保存されていることを確認
        // Sessionクラスの内部構造に依存するが、FLASH_NEXTに入っているはず
        $this->assertNotEmpty($this->sessionData);
        
        // 2. 次のリクエスト（リダイレクト先）をシミュレート
        // 同じセッションデータ配列を使って別のAppインスタンスを作成
        $app2 = $this->createApp();
        $request2 = $this->createRequest('GET', '/samples/flashes/get');
        $response2 = $app2->handle($request2);

        $html2 = (string)$response2->getBody();
        $this->assertStringContainsString('This notice is set in onBack method.', $html2);
        $this->assertStringContainsString('This message is set in onBack method.', $html2);
    }
}
