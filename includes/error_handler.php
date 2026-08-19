<?php
/**
 * Legacy compatibility shim for error_handler.php.
 *
 * Existing code that requires error_handler.php continues to work.
 * New code should use includes/error-handler.php directly.
 */

if (!defined('ERROR_LOG_FILE')) {
    define('ERROR_LOG_FILE', __DIR__ . '/../error.log');
}

// Load the new error handler if not already loaded.
if (!function_exists('voltra_log')) {
    require_once __DIR__ . '/error-handler.php';
}

if (!function_exists('register_error_handler')) {
    function register_error_handler(): void {}
}

// Register the new handler on include.
register_error_handler();

if (!function_exists('writeErrorLog')) {
    function writeErrorLog(Throwable $e, string $type = 'Exception'): void {
        voltra_log('error', "[$type] " . $e->getMessage(), [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}

if (!function_exists('shpExceptionHandler')) {
    function shpExceptionHandler(Throwable $e) {
        writeErrorLog($e, 'Exception: ' . get_class($e));
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            if (session_status() === PHP_SESSION_NONE) { @session_start(); }
            if (!isset($_SESSION['error'])) {
                $_SESSION['error'] = 'An unexpected error occurred. Our team has been notified.';
            }
            $isAdmin = isset($_SESSION['admin_id']);
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
                    . '<title>Error</title>'
                    . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">'
                    . '</head><body class="bg-light"><div class="container py-5">'
                    . '<div class="alert alert-danger shadow-sm"><h4 class="alert-heading">Something went wrong</h4>'
                    . '<p class="mb-0">An unexpected error occurred while processing your request. '
                    . 'The issue has been logged and our team will investigate.</p></div>'
                    . ($isAdmin ? '<a href="index.php" class="btn btn-primary">Back to Dashboard</a>' : '<a href="../index.php" class="btn btn-primary">Back to Home</a>')
                    . '</div></body></html>';
            }
        }
    }
}

if (!function_exists('shpErrorHandler')) {
    function shpErrorHandler($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        if (in_array($errno, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)) {
            return false;
        }
        $map = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];
        $type = $map[$errno] ?? 'Error';
        writeErrorLog(new RuntimeException($errstr), $type);
        return false;
    }
}

if (!function_exists('shpShutdownHandler')) {
    function shpShutdownHandler() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            writeErrorLog(new RuntimeException($error['message']), 'Fatal Error (shutdown)');
        }
    }
}

set_exception_handler('shpExceptionHandler');
set_error_handler('shpErrorHandler');
register_shutdown_function('shpShutdownHandler');
