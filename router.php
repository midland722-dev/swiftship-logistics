<?php
/**
 * Router script for Railway PHP built-in server.
 *
 * Usage: php -S 0.0.0.0:$PORT -t . router.php
 *
 * This handles rewrites that would normally be in .htaccess.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// If the request is for a real file or directory, let the server handle it
if ($uri !== '/' && file_exists($file)) {
    return false;
}

// Redirect /admin to the PHP login page
if ($uri === '/admin' || $uri === '/admin/') {
    header('Location: /deprixa/login.php', true, 302);
    exit;
}

// For all other non-file requests, serve the React app's index.html
if (file_exists(__DIR__ . '/index.html')) {
    require __DIR__ . '/index.html';
    exit;
}

// Fallback
http_response_code(404);
echo 'Not Found';
