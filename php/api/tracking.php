<?php
/**
 * Tracking API routes
 *
 * GET  /api/tracking.php?id={tracking_number}  - track shipment
 * POST /api/tracking.php?action=status          - update status (admin/staff)
 * POST /api/tracking.php?action=create         - create shipment (admin/staff)
 * GET  /api/tracking.php?action=history&id={tracking_number} - get history
 */

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/TrackingController.php';

csrf_same_origin_guard();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'history') {
    $trackingNumber = trim($_GET['id'] ?? '');
    $errors = TrackingValidator::validateTrackingNumber($trackingNumber);
    if ($errors) {
        json_error(implode(' ', $errors), 422, 'VALIDATION_ERROR');
    }
    $events = TrackingController::history($trackingNumber);
    json_success(array_map(fn($e) => $e->toArray(), $events));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action)) {
    TrackingController::track();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'status') {
    require_role(['admin', 'staff']);
    TrackingController::statusUpdate();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    require_role(['admin', 'staff']);
    TrackingController::createShipment();
}

json_error('Method not allowed or invalid action.', 405, 'METHOD_NOT_ALLOWED');
