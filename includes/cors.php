<?php
/**
 * CORS helper for American Shipping & Logistics API endpoints.
 *
 * Usage:
 *   require_once __DIR__ . '/cors.php';
 *   // The helper will send appropriate CORS headers and exit on preflight.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowedOrigins = [
    'http://localhost',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:8080',
    'http://localhost:8080',
];

if (defined('APP_URL')) {
    $allowedOrigins[] = APP_URL;
    $parsed = parse_url(APP_URL);
    if (!empty($parsed['host'])) {
        $allowedOrigins[] = $parsed['scheme'] . '://' . $parsed['host'] . ':5173';
        $allowedOrigins[] = $parsed['scheme'] . '://' . $parsed['host'] . ':8080';
    }
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
