<?php

namespace Tests\Core\Unit\Services;

use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\Randval;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use Aura\Session\SessionFactory;
use PHPUnit\Framework\TestCase;
use WScore\Deca\Services\SessionAura;

class SessionAuraTest extends TestCase
{
    /**
     * @var SessionAura
     */
    private $session;

    protected function setUp(): void
    {
        // Mock Phpfunc to avoid session_start() headers already sent error in PHPUnit
        $phpfunc = $this->createMock(Phpfunc::class);
        $phpfunc->method('__call')->willReturnCallback(function($func, $args) {
            if ($func === 'session_start') return true;
            if ($func === 'session_status') return PHP_SESSION_ACTIVE;
            if ($func === 'session_get_cookie_params') return ['lifetime' => 0, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false];
            if ($func === 'session_name') return 'PHPSESSID';
            if ($func === 'function_exists' && $args[0] === 'random_bytes') return true;
            if ($func === 'random_bytes') return random_bytes($args[0]);
            return true;
        });

        // Replicate SessionFactory::newInstance but with mocked Phpfunc
        $session = new Session(
            new SegmentFactory(),
            new CsrfTokenFactory(new Randval($phpfunc)),
            $phpfunc,
            []
        );

        $factory = $this->createMock(SessionFactory::class);
        $factory->method('newInstance')->willReturn($session);

        $this->session = new SessionAura($factory);
    }

    public function testCsrfToken(): void
    {
        $token = $this->session->getCsRfToken();
        $this->assertNotEmpty($token);
        $this->assertTrue($this->session->validateCsRfToken($token));
        $this->assertFalse($this->session->validateCsRfToken('invalid-token'));

        $this->session->regenerateCsRfToken();
        $newToken = $this->session->getCsRfToken();
        $this->assertNotEquals($token, $newToken);
        $this->assertTrue($this->session->validateCsRfToken($newToken));
    }

    public function testCsrfTokenName(): void
    {
        $this->assertEquals('_csrf_token', $this->session->getCsRfTokenName());
        $this->session->setCsrfTokenName('custom_csrf');
        $this->assertEquals('custom_csrf', $this->session->getCsRfTokenName());
    }

    public function testSaveAndLoad(): void
    {
        $this->session->save('key1', 'value1');
        $this->assertEquals('value1', $this->session->load('key1'));
        $this->assertNull($this->session->load('unknown'));
    }

    public function testClearFlash(): void
    {
        $this->session->setFlash('notice', 'hello');
        $this->assertEquals('hello', $this->session->getFlash('notice'));

        // Clear specific key
        $this->session->clearFlash('notice');
        $this->assertNull($this->session->getFlash('notice'));

        // Clear all
        $this->session->setFlash('info', 'val');
        $this->session->clearFlash();
        $this->assertNull($this->session->getFlash('info'));
    }

    public function testKeepFlash(): void
    {
        $this->session->setFlash('keep-me', 'val');
        // setFlash in SessionAura uses setFlashNow(), so it's in now.
        // Aura's keepFlash() copies from now to next.
        $this->session->keepFlash('keep-me');
        $this->assertEquals('val', $this->session->getFlash('keep-me'));
    }

    public function testFlashDefaultValue(): void
    {
        $this->assertEquals('default', $this->session->getFlash('non-existent', 'default'));
    }
}
