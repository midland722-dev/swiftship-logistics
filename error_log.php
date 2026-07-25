<?php
/**
 * Quick Access Error Log
 * ----------------------
 * Centralized error log for the application.
 *
 * Log file: /shp/errors.log
 *
 * Usage:
 *   require_once __DIR__ . '/error_log.php';
 *   log_error('Database connection failed', ['host' => $host, 'error' => $e->getMessage()]);
 *
 * The log file is plain text, one JSON entry per line, for easy reading and grep.
 */

function log_error($message, $context = [], $level = 'ERROR') {
    $log_file = __DIR__ . '/errors.log';

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => strtoupper($level),
        'message' => $message,
        'context' => $context,
        'user_id' => $_SESSION['admin_id'] ?? null,
        'user_name' => $_SESSION['admin_username'] ?? ($_SESSION['user_name'] ?? 'guest'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli',
        'url' => $_SERVER['REQUEST_URI'] ?? 'cli',
    ];

    $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

    if (file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX) === false) {
        error_log("Failed to write to error log: $log_file");
    }
}

function log_warning($message, $context = []) {
    log_error($message, $context, 'WARNING');
}

function log_info($message, $context = []) {
    log_error($message, $context, 'INFO');
}

function log_debug($message, $context = []) {
    log_error($message, $context, 'DEBUG');
}

function get_recent_errors($limit = 50) {
    $log_file = __DIR__ . '/errors.log';
    if (!file_exists($log_file)) {
        return [];
    }

    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines);
    $entries = [];

    foreach (array_slice($lines, 0, $limit) as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $entries[] = $entry;
        }
    }

    return $entries;
}

function clear_error_log() {
    $log_file = __DIR__ . '/errors.log';
    if (file_exists($log_file)) {
        file_put_contents($log_file, '');
    }
}
