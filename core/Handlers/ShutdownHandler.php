<?php
declare(strict_types=1);

namespace WScore\Deca\Handlers;

use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ShutdownHandler
{
    /**
     * @var string
     */
    private $log_file;

    /**
     * @var string
     */
    private $raw_error_file;

    /**
     * @var bool
     */
    private $displayErrorDetails = false;

    /**
     * @var bool
     */
    private $debug = false;

    public function __construct(string $raw_error_file, ?string $log_file = null)
    {
        $this->log_file = $log_file;
        $this->raw_error_file = $raw_error_file;
    }

    public static function forgeRaw(?string $template_dir = null, ?string $log_file = null): ShutdownHandler
    {
        $template_dir = $template_dir ?? dirname(__DIR__, 1) . '/templates';
        $raw_error_file = $template_dir . '/raw-error.php';
        $log_file = $log_file ?? dirname($template_dir) . '/var/raw-error.log';

        return new self($raw_error_file, $log_file);
    }

    public function setDebug(bool $debug = false): ShutdownHandler
    {
        $this->debug = $debug;
        return $this;
    }

    public function setDisplayErrorDetails(bool $displayErrorDetails = false): ShutdownHandler
    {
        $this->displayErrorDetails = $displayErrorDetails;
        return $this;
    }

    public function __invoke(Throwable $throwable)
    {
        $this->log($throwable);
        if ($this->debug) {
            $this->whoops($throwable);
            return;
        } elseif($this->displayErrorDetails) {
            $this->rawError($throwable);
            return;
        }
        $this->rawError(null);
    }

    private function rawError(?Throwable $throwable = null)
    {
        include $this->raw_error_file;
    }

    private function whoops(Throwable $throwable)
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        echo $whoops->handleException($throwable);
    }

    private function log(Throwable $throwable)
    {
        if ($this->log_file) {
            $now = date('Y-m-d H:i:s');
            $message = <<<END_TEXT
------------
{$now}
{$throwable->__toString()}

END_TEXT;

            file_put_contents($this->log_file, $message, FILE_APPEND | LOCK_EX);
        }
    }
}