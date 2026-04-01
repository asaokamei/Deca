<?php

namespace Tests\Core\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Services\Session;

class MessagesTest extends TestCase
{
    public function testAddMessageWithNewSession()
    {
        $data = [
            '_flash_next' => ['success' => ['Old Message']]
        ];
        $session = new Session($data);
        // During Session constructor, aging happened:
        // _flash_now = ['success' => ['Old Message']], _flash_next = []
        
        $messages = new Messages($session);

        // First call to addMessage
        $messages->addSuccess('New Message 1');
        
        // Session::getFlash('success') returns _flash_now['success'] which is ['Old Message'].
        // Then it sets _flash_next['success'] = ['Old Message', 'New Message 1']
        $this->assertEquals(['Old Message', 'New Message 1'], $data['_flash_next']['success']);

        // Second call to addMessage
        $messages->addSuccess('New Message 2');
        // Session::getFlash('success') returns _flash_next['success'] (because now it has priority or fallback)
        // it returns ['Old Message', 'New Message 1']
        // It appends 'New Message 2' and sets it to _flash_next.
        $this->assertEquals(['Old Message', 'New Message 1', 'New Message 2'], $data['_flash_next']['success']);
    }

    public function testGetMessages()
    {
        $data = [
            '_flash_next' => ['success' => ['Message To Read']]
        ];
        $session = new Session($data);
        // Aging moves it to _flash_now
        $messages = new Messages($session);

        // Messages::getMessages now calls getFlash() then clearFlash()
        $result = $messages->getMessages('success');
        
        $this->assertEquals(['Message To Read'], $result);
        $this->assertEquals([], $data['_flash_now']);
    }
}
