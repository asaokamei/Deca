<?php

namespace Tests\Core\Unit\Services;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Services\Session;

class SessionTest extends TestCase
{
    public function testSaveAndLoad()
    {
        $data = [];
        $session = new Session($data);
        $session->save('foo', 'bar');
        $this->assertEquals('bar', $session->load('foo'));
        $this->assertEquals('bar', $data['foo']);
    }

    public function testFlashAgingOnConstructor()
    {
        $data = [
            '_flash_next' => ['msg' => 'hello']
        ];
        // Constructor triggers aging
        $session = new Session($data);

        $this->assertEquals(['msg' => 'hello'], $data['_flash_now']);
        $this->assertEquals([], $data['_flash_next']);

        $this->assertEquals('hello', $session->getFlash('msg'));
        
        // setFlash should go to _flash_next
        $session->setFlash('next', 'world');
        $this->assertEquals('world', $data['_flash_next']['next']);
        
        // getFlash should see the value set in current request too
        $this->assertEquals('world', $session->getFlash('next'));
    }

    public function testSetFlash()
    {
        $data = [];
        $session = new Session($data);
        $session->setFlash('info', 'saved');
        $this->assertEquals('saved', $data['_flash_next']['info']);
        // getFlash should return 'saved' even if it's in FLASH_NEXT
        $this->assertEquals('saved', $session->getFlash('info'));
    }

    public function testCsrfToken()
    {
        $data = [];
        $session = new Session($data);
        $token = $session->getCsRfToken();
        $this->assertNotEmpty($token);
        $this->assertTrue($session->validateCsRfToken($token));
        $this->assertFalse($session->validateCsRfToken('wrong'));

        $session->regenerateCsRfToken();
        $newToken = $session->getCsRfToken();
        $this->assertNotEquals($token, $newToken);
        $this->assertTrue($session->validateCsRfToken($newToken));
    }

    public function testClearFlash()
    {
        $data = [
            '_flash_next' => ['b' => 2]
        ];
        $session = new Session($data);
        // After constructor aging:
        // _flash_now = ['b' => 2]
        // _flash_next = []
        $this->assertEquals(['b' => 2], $data['_flash_now']);
        $this->assertEmpty($data['_flash_next']);

        // Clear all (now)
        $session->clearFlash();
        $this->assertEmpty($data['_flash_now']);

        // Set for next and clear specific key
        $session->setFlash('c', 3);
        $this->assertEquals(3, $data['_flash_next']['c']);
        $session->clearFlash('c');
        $this->assertArrayNotHasKey('c', $data['_flash_next']);
    }

    public function testKeepFlash()
    {
        $data = [
            '_flash_next' => ['msg' => 'hello']
        ];
        // Constructor ages it to _flash_now
        $session = new Session($data);
        $this->assertEquals(['msg' => 'hello'], $data['_flash_now']);
        $this->assertEquals([], $data['_flash_next']);

        // Keep it
        $session->keepFlash('msg');
        $this->assertEquals(['msg' => 'hello'], $data['_flash_next']);

        // Keep all
        $data['_flash_now']['another'] = 'world';
        $session->keepFlash();
        $this->assertEquals(['msg' => 'hello', 'another' => 'world'], $data['_flash_next']);
    }
}
