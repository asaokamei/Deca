<?php
declare(strict_types=1);

namespace Tests\Demo\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use WScore\Deca\Adapters\Session\Session;

class FlashControllerTest extends HttpTestCase
{
    public function test_onGet_displays_flash_page()
    {
        $app = $this->createApp();
        $request = $this->createRequest('GET', '/samples/flashes/get');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('Flash Messages', $html);
    }

    public function test_default_method_is_get()
    {
        $app = $this->createApp();
        // method引数なしのURL
        $request = $this->createRequest('GET', '/samples/flashes/');
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
