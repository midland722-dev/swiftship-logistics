<?php
/**
 * AJAX / JSON endpoint for live tracking lookups.
 *
 * GET  /process/track_ajax.php?id=LX-2024-001
 *
 * Delegates to TrackingController for consistent behavior.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/rate-limit.php';

// Rate limit: 120 tracking lookups per minute per IP
if (!rate_limit('tracking_api', 120, 60)) {
    http_response_code(429);
    echo json_encode(['found' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../controllers/TrackingController.php';

try {
    TrackingController::track();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['found' => false, 'message' => 'Server error.', 'debug' => $e->getMessage()]);
}
