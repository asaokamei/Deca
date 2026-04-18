<?php
declare(strict_types=1);

namespace Tests\Http\Demo;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use WScore\Deca\Services\Session;
use Nyholm\Psr7\Factory\Psr17Factory;

class FormControllerTest extends TestCase
{
    private array $sessionData = [];

    protected function setUp(): void
    {
        $this->sessionData = [];
    }

    private function createRequest(string $method, string $path, array $postData = []): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        $uri = $factory->createUri($path);
        $request = $factory->createServerRequest($method, $uri);

        if ($method === 'POST') {
            $request = $request->withParsedBody($postData);
        }

        return $request;
    }

    private function createApp(bool $displayErrors = false): App
    {
        require_once __DIR__ . '/../../../appDemo/boot.php';

        $setting = getSettings(__DIR__ . '/../../../settings.ini');
        $setting->addSettings(['display_errors' => $displayErrors]);

        $definitions = getDefinitions($setting);
        $definitions->setValue(Session::class, new Session($this->sessionData));
        $container = getContainer($definitions);

        $app = getApp($container);
        registerRoutes($app);

        return $app;
    }

    public function test_onGet_displays_form()
    {
        $app = $this->createApp();
        $request = $this->createRequest('GET', '/samples/form');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('name="name"', $html);
    }

    public function test_onPost_with_error_param()
    {
        $app = $this->createApp();
        
        // CSRFトークンを取得するために一度GETリクエストを投げるか、セッションに直接トークンを仕込む
        $session = new Session($this->sessionData);
        $token = $session->getCsRfToken();
        $tokenName = $session->getCsRfTokenName();

        $postData = [
            $tokenName => $token,
            'with_error' => '1',
            'name' => 'Test User',
        ];

        $request = $this->createRequest('POST', '/samples/form', $postData);
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('Post with Error!', $html);
        $this->assertStringContainsString('This is an error message for name.', $html);
    }

    public function test_onPost_validation_failed_displays_form_with_errors()
    {
        $app = $this->createApp();
        
        $session = new Session($this->sessionData);
        $token = $session->getCsRfToken();
        $tokenName = $session->getCsRfTokenName();

        // 必須項目（languageなど）を抜いてバリデーション失敗させる
        $postData = [
            $tokenName => $token,
            'name' => 'Test User',
            'language' => '', // required error
        ];

        $request = $this->createRequest('POST', '/samples/form', $postData);
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('Post is invalidated!', $html);
        $this->assertStringContainsString('Select language', $html);
    }

    public function test_onPost_validation_failed_with_redirect()
    {
        $app = $this->createApp();
        
        $session = new Session($this->sessionData);
        $token = $session->getCsRfToken();
        $tokenName = $session->getCsRfTokenName();

        $postData = [
            $tokenName => $token,
            'redirect' => '1',
            'language' => '', // validation error
        ];

        $request = $this->createRequest('POST', '/samples/form', $postData);
        $response = $app->handle($request);

        // リダイレクトの確認
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        
        // セッションにエラーメッセージが残っているか確認するために、リダイレクト先をハンドル
        $app2 = $this->createApp();
        $request2 = $this->createRequest('GET', '/samples/form');
        $response2 = $app2->handle($request2);
        
        $html2 = (string)$response2->getBody();
        $this->assertStringContainsString('Post is invalidated!', $html2);
        $this->assertStringContainsString('Redirected back to the input form...', $html2);
    }

    public function test_onPost_success()
    {
        $app = $this->createApp();
        
        $session = new Session($this->sessionData);
        $token = $session->getCsRfToken();
        $tokenName = $session->getCsRfTokenName();

        $postData = [
            $tokenName => $token,
            'name' => 'Test User',
            'language' => 'ja',
            'dev' => [
                'framework' => 'SLIM',
                'ai' => ['GEMINI'],
            ],
            'profile' => [
                'email' => 'test@example.com',
                'birthday' => '2000-01-01',
            ],
        ];

        $request = $this->createRequest('POST', '/samples/form', $postData);
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertStringContainsString('Post accepted!', $html);
        $this->assertStringContainsString('Input validated...', $html);
    }

    public function test_onPost_csrf_failure()
    {
        // SimpleErrorHandler を確実に使うために displayErrors = false
        $app = $this->createApp(false);
        
        $postData = [
            'name' => 'Test User',
            // CSRFトークンなし
        ];

        $request = $this->createRequest('POST', '/samples/form', $postData);
        $response = $app->handle($request);

        // CsRfGuardがHttpForbiddenExceptionを投げるはず
        $this->assertEquals(403, $response->getStatusCode());
        // Slim のデフォルトの ErrorHandler もしくは SimpleErrorHandler のタイトルを期待
        $html = (string)$response->getBody();
        $this->assertTrue(
            str_contains($html, 'Access Not Allowed') || 
            str_contains($html, 'You are not permitted to perform the requested operation.') ||
            str_contains($html, '403 Forbidden')
        );
    }
}
