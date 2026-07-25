<?php
/**
 * Simple in-memory rate limiter.
 *
 * For shared hosting, this uses the filesystem instead of memory
 * so it works across requests without Redis/Memcached.
 *
 * Usage:
 *   require_once __DIR__ . '/rate-limit.php';
 *   rate_limit('tracking_api', 60); // 60 requests per minute
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * @param string $key        Limiter key (e.g. 'tracking_api', 'contact_form')
 * @param int    $maxRequests Maximum requests allowed in the window
 * @param int    $windowSeconds Time window in seconds (default 60)
 * @return bool  True if allowed, false if rate limited
 */
function rate_limit(string $key, int $maxRequests = 60, int $windowSeconds = 60): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userId = $_SESSION['user_id'] ?? 'guest';
    $identifier = $key . ':' . $userId . ':' . $ip;

    $dir = sys_get_temp_dir() . '/ascl_rate_limit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $file = $dir . '/' . md5($identifier) . '.json';
    $now = time();

    $data = ['window_start' => $now, 'count' => 0];
    if (file_exists($file)) {
        $content = @file_get_contents($file);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
    }

    if ($now - (int)($data['window_start'] ?? $now) >= $windowSeconds) {
        $data = ['window_start' => $now, 'count' => 0];
    }

    $data['count'] = (int)($data['count'] ?? 0) + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > $maxRequests) {
        return false;
    }

    return true;
}

/**
 * Enforce rate limit or return 429 JSON response.
 */
function require_rate_limit(string $key, int $maxRequests = 60, int $windowSeconds = 60): void {
    if (!rate_limit($key, $maxRequests, $windowSeconds)) {
        json_error('Too many requests. Please slow down.', 429, 'RATE_LIMIT_EXCEEDED');
    }
}
