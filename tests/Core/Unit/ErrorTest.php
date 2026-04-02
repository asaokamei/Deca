<?php
declare(strict_types=1);

namespace tests\Core\Unit;

use PHPUnit\Framework\TestCase;
use ErrorException;

class ErrorTest extends TestCase
{
    private $old_error_handler;
    private $old_exception_handler;

    protected function setUp(): void
    {
        $this->old_error_handler = set_error_handler(null);
        $this->old_exception_handler = set_exception_handler(null);
        restore_error_handler();
        restore_exception_handler();
    }

    protected function tearDown(): void
    {
        set_error_handler($this->old_error_handler);
        set_exception_handler($this->old_exception_handler);
    }

    public function test_error_handler_converts_warning_to_exception()
    {
        // error.php を読み込んでハンドラを設定させる
        // すでに bootstrap.php などで読み込まれている可能性があるが、
        // 明示的に読み込む（再定義エラーに注意が必要だが、error.php は関数定義ではなく実行文）
        require __DIR__ . '/../../../appDemo/error.php';

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("Test Warning");

        trigger_error("Test Warning", E_USER_WARNING);
    }

    public function test_exception_handler_is_set()
    {
        require __DIR__ . '/../../../appDemo/error.php';

        $handler = set_exception_handler(null);
        $this->assertIsCallable($handler);
        // 元に戻す
        set_exception_handler($handler);
    }
}
