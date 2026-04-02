<?php
declare(strict_types=1);

namespace tests\Core\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use WScore\Deca\Handlers\ShutdownHandler;
use Exception;
use ReflectionMethod;

class ShutdownHandlerTest extends TestCase
{
    private string $log_file;
    private string $template_file;
    private string $template_dir;

    protected function setUp(): void
    {
        $this->template_dir = sys_get_temp_dir() . '/deca_test_templates_' . uniqid();
        if (!is_dir($this->template_dir)) {
            mkdir($this->template_dir, 0777, true);
        }
        $this->template_file = $this->template_dir . '/raw-error.php';
        file_put_contents($this->template_file, '<?php echo "Error: " . ($throwable ? $throwable->getMessage() : "none"); ?>');

        $this->log_file = sys_get_temp_dir() . '/deca_test_shutdown_' . uniqid() . '.log';
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        if (file_exists($this->template_file)) {
            unlink($this->template_file);
        }
        if (is_dir($this->template_dir)) {
            rmdir($this->template_dir);
        }
    }

    public function test_construct_and_invoke()
    {
        $handler = new ShutdownHandler($this->template_file, $this->log_file);
        $handler->setDisplayErrorDetails(true);

        $exception = new Exception("Test Invoke Exception");
        
        ob_start();
        $handler($exception);
        $output = ob_get_clean();

        $this->assertStringContainsString("Error: Test Invoke Exception", $output);
        $this->assertFileExists($this->log_file);
        $this->assertStringContainsString("Test Invoke Exception", file_get_contents($this->log_file));
    }

    public function test_forgeRaw()
    {
        // 修正後の forgeRaw は $template_dir と $log_file を受け取れる
        $handler = ShutdownHandler::forgeRaw($this->template_dir, $this->log_file);
        $this->assertInstanceOf(ShutdownHandler::class, $handler);

        $exception = new Exception("Test ForgeRaw Exception");
        $handler->setDisplayErrorDetails(true);

        ob_start();
        $handler($exception);
        $output = ob_get_clean();

        $this->assertStringContainsString("Error: Test ForgeRaw Exception", $output);
        $this->assertFileExists($this->log_file);
    }

    public function test_rawError_with_null()
    {
        $handler = new ShutdownHandler($this->template_file, $this->log_file);
        
        $refl = new ReflectionMethod($handler, 'rawError');
        // $refl->setAccessible(true); // Deprecated in PHP 8.5

        ob_start();
        $refl->invoke($handler, null);
        $output = ob_get_clean();

        $this->assertStringContainsString("Error: none", $output);
    }

    public function test_invoke_without_details()
    {
        $handler = new ShutdownHandler($this->template_file, $this->log_file);
        $handler->setDisplayErrorDetails(false);

        $exception = new Exception("Should Not Be Shown");

        ob_start();
        $handler($exception);
        $output = ob_get_clean();

        $this->assertStringContainsString("Error: none", $output);
        $this->assertFileExists($this->log_file);
        $this->assertStringContainsString("Should Not Be Shown", file_get_contents($this->log_file));
    }
}
