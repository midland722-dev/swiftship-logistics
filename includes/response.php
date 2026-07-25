<?php
/**
 * Standardized JSON response helpers for American Shipping & Logistics API endpoints.
 *
 * Usage:
 *   require_once __DIR__ . '/response.php';
 *   json_success($data, 200);
 *   json_error('Not found', 404, 'NOT_FOUND');
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns a JSON success response and exits.
 *
 * @param mixed  $data       Response payload
 * @param int    $statusCode HTTP status code
 * @param string $message    Optional message
 */
function json_success(mixed $data = null, int $statusCode = 200, string $message = 'OK'): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

/**
 * Returns a JSON error response and exits.
 *
 * @param string      $message    Human-readable error message
 * @param int         $statusCode HTTP status code
 * @param string|null $code       Machine-readable error code
 * @param array|null  $errors     Field-level validation errors
 */
function json_error(string $message, int $statusCode = 400, ?string $code = null, ?array $errors = null): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'code'    => $code ?? 'ERROR',
        'errors'  => $errors,
    ]);
    exit;
}
