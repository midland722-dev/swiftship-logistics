<?php
// Router for the PHP built-in server (Railway). Serves existing files directly,
// keeps a health endpoint available, and falls back to the panel entry point.
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;

if ($uri === '/healthz' || $uri === '/healthz/') {
    http_response_code(200);
    header('Content-Type: text/plain');
    echo 'ok';
    exit;
}

if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false; // let the built-in server serve the file
}

if (is_dir($path)) {
    foreach (['index.php', 'index.html'] as $indexFile) {
        if (file_exists(rtrim($path, '/') . '/' . $indexFile)) {
            require rtrim($path, '/') . '/' . $indexFile;
            return true;
        }
    }
}

header('Location: /deprixa/login.php');
