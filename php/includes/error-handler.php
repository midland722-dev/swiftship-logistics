<?php
/**
 * Centralized error handler and logger for American Shipping & Logistics.
 *
 * Usage:
 *   require_once __DIR__ . '/error-handler.php';
 *   register_error_handler();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Logs a message to the application log file.
 *
 * @param string $level   Log level: info, warning, error, critical
 * @param string $message Log message
 * @param array  $context Additional context data
 */
function voltra_log(string $level, string $message, array $context = []): void {
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $userId = $_SESSION['user_id'] ?? null;
    $contextStr = !empty($context) ? json_encode($context) : '';
    
    $line = sprintf(
        "[%s] [%s] [user_id=%s] [ip=%s] %s %s\n",
        $date,
        strtoupper($level),
        $userId ?? 'guest',
        $ip,
        $message,
        $contextStr
    );

    $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Registers the global error and exception handlers.
 */
function register_error_handler(): void {
    $isProduction = getenv('APP_ENV') === 'production';
    
    // Error handler
    set_error_handler(function ($severity, $message, $file, $line) use ($isProduction) {
        if (!(error_reporting() & $severity)) {
            return;
        }
        
        voltra_log('error', "$message in $file on line $line", [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
        ]);
        
        if ($isProduction) {
            http_response_code(500);
            echo 'An internal error occurred. Please try again later.';
            exit;
        }
        
        return false;
    });
    
    // Exception handler
    set_exception_handler(function (Throwable $e) use ($isProduction) {
        voltra_log('critical', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        if ($isProduction) {
            http_response_code(500);
            echo 'An internal error occurred. Please try again later.';
            exit;
        }
        
        echo '<pre>';
        echo 'Uncaught exception: ' . htmlspecialchars($e->getMessage()) . "\n";
        echo 'File: ' . htmlspecialchars($e->getFile()) . "\n";
        echo 'Line: ' . $e->getLine() . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
        exit;
    });
    
    // Shutdown handler for fatal errors
    register_shutdown_function(function () use ($isProduction) {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            voltra_log('critical', 'Fatal error: ' . $error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
            ]);
            
            if ($isProduction) {
                http_response_code(500);
                echo 'An internal error occurred. Please try again later.';
                exit;
            }
        }
    });
}
