<?php
// router.php - Simple router for Railway PHP deployment
// This file routes all requests to the appropriate handler

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Handle API routes
if (str_starts_with($path, '/api/')) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        return require $file;
    }
}

// Handle PHP admin panel routes
if (str_starts_with($path, '/deprixa/') || str_starts_with($path, '/php/')) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        return require $file;
    }
}

// Handle root and other PHP files
if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    return;
}

// For all other requests, serve index.html (React SPA)
if (file_exists(__DIR__ . '/index.html')) {
    readfile(__DIR__ . '/index.html');
    return;
}

// Fallback
http_response_code(404);
echo "Not Found";
