<?php
declare(strict_types=1);

use WScore\Deca\Handlers\ShutdownHandler;

ini_set("display_errors", '0');
ini_set("display_startup_errors", '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

/**
 * error handling for warnings, etc.
 * @throws ErrorException
 */
set_error_handler(
    function ($error_no, $error_msg, $error_file, $error_line) {
        if (error_reporting() === 0) {
            return;
        }
        throw new ErrorException($error_msg, 0, $error_no, $error_file, $error_line);
    }
);

/**
 * if PHP shuts down with error, throw an exception.
 * @throws ErrorException
 */
register_shutdown_function(
    function () {
        $error = error_get_last();
        if ($error === null) {
            return;
        }
        throw new ErrorException($error['message'], 0, 0, $error['file'], $error['line']);
    }
);

/**
 * handle uncaught exception
 */
set_exception_handler(
    ShutdownHandler::forgeRaw()
        ->setDebug(false)
        ->setDisplayErrorDetails(true)
);


