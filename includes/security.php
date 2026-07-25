<?php
/**
 * Security headers and CSP for production PHP pages.
 *
 * Usage:
 *   require_once __DIR__ . '/security.php';
 *   send_security_headers();
 */

function send_security_headers(): void {
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // XSS protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Content Security Policy
    // Allows: same-origin, inline styles (needed for existing code), 
    // Tailwind CDN, Google Fonts, Lucide icons (inline SVG)
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com",
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: https:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
    
    // HSTS — only send over HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    
    // CORS: allow local dev + configurable production origins
    $appUrl  = getenv('APP_URL') ?: '';
    $parsed  = $appUrl ? parse_url($appUrl) : [];
    $appHost = $parsed['host'] ?? '';
    
    $allowedOrigins = array_filter([
        'http://localhost',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        $appHost ? ('https://' . $appHost) : '',
        $appHost ? ('http://' . $appHost) : '',
    ]);
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin && in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
