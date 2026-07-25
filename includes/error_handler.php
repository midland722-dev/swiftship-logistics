<?php
/**
 * Centralized Error Handler
 * --------------------------
 * Registers global exception/error/shutdown handlers that append every
 * error to error.log in a structured, human-readable format:
 *
 *   [2026-07-11 14:20:32]
 *   Tracking: TRK92837465
 *   Shipment ID: 105
 *   User: Admin
 *   Type: Database Error
 *   Message: Failed to update shipment status
 *   File: ShipmentController.php
 *   Line: 243
 *
 * Best-effort: if the log file is not writable the handler silently no-ops
 * so it can never break the running application.
 */

if (!defined('ERROR_LOG_FILE')) {
    define('ERROR_LOG_FILE', __DIR__ . '/../error.log');
}

if (!function_exists('writeErrorLog')) {
    function writeErrorLog(Throwable $e, string $type = 'Exception') {
        $file = ERROR_LOG_FILE;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            return;
        }

        $user = 'Guest';
        if (session_status() === PHP_SESSION_ACTIVE) {
            $user = $_SESSION['admin_name'] ?? ($_SESSION['user_name'] ?? 'Guest');
        }
        $shipmentId = $GLOBALS['current_shipment_id']
            ?? ($_REQUEST['id'] ?? ($_REQUEST['shipment_id'] ?? null));
        $tracking = $GLOBALS['current_tracking_number']
            ?? ($_REQUEST['tracking_number'] ?? ($_REQUEST['tracking'] ?? null));

        $stamp = date('Y-m-d H:i:s');
        $lines = [];
        $lines[] = "[$stamp]";
        $lines[] = 'Tracking: ' . ($tracking !== null ? $tracking : 'N/A');
        $lines[] = 'Shipment ID: ' . ($shipmentId !== null ? $shipmentId : 'N/A');
        $lines[] = 'User: ' . $user;
        $lines[] = 'Type: ' . $type;
        $lines[] = 'Message: ' . $e->getMessage();
        $lines[] = 'File: ' . basename($e->getFile());
        $lines[] = 'Line: ' . $e->getLine();
        $lines[] = "Stack Trace:\n" . $e->getTraceAsString();
        $lines[] = str_repeat('-', 60);

        @file_put_contents($file, implode("\n", $lines) . "\n\n", FILE_APPEND);
    }
}

if (!function_exists('shpExceptionHandler')) {
    function shpExceptionHandler(Throwable $e) {
        writeErrorLog($e, 'Exception: ' . get_class($e));
        // Let the existing error_log.php / Logger infra also record it.
        if (function_exists('log_error')) {
            try { log_error('Uncaught ' . get_class($e), $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]); } catch (Throwable $ignored) {}
        }
        // Display a user-friendly message in web context (requirement 11).
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
        // Skip non-fatal noise (notices/deprecations) to keep error.log focused.
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
        $fake = new RuntimeException($errstr);
        writeErrorLog($fake, $type);
        return false; // allow PHP's standard handler to continue
    }
}

if (!function_exists('shpShutdownHandler')) {
    function shpShutdownHandler() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $fake = new RuntimeException($error['message']);
            writeErrorLog($fake, 'Fatal Error (shutdown)');
        }
    }
}

set_exception_handler('shpExceptionHandler');
set_error_handler('shpErrorHandler');
register_shutdown_function('shpShutdownHandler');
