<?php

namespace Tests\Core\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Controllers\Messages;
use WScore\Deca\Services\Session;

class MessagesSessionIntegrationTest extends TestCase
{
    public function testMessagesPersistenceWhenRenderedInSameRequest()
    {
        // 1. 最初の小リクエスト
        $sessionData = [];
        $session = new Session($sessionData);
        $messages = new Messages($session);

        // メッセージを追加（FLASH_NEXTに入る）
        $messages->addSuccess('First message');

        // 同じリクエスト内でメッセージを取得（View表示の想定）
        $retrieved = $messages->getMessages('success');
        $this->assertEquals(['First message'], $retrieved);

        // 2. 次のリクエストをシミュレート
        // 前のリクエスト終了時の $sessionData を引き継ぐ
        $session2 = new Session($sessionData);
        $messages2 = new Messages($session2);

        // 期待値: 前のリクエストで getMessages したので、次のリクエストでは空であるべき
        // 現状: FLASH_NEXT に残ったままなので、ageFlash で FLASH_NOW に移動し、再度表示されるはず
        $retrieved2 = $messages2->getMessages('success');
        
        $this->assertEmpty($retrieved2, 'Messages should be empty in the next request after being retrieved in the current request');
    }
}
