<?php
/**
 * Status update API
 *
 * POST /api/status-update.php
 * Body: {
 *   shipment_id: int,
 *   tracking_number: string,
 *   status: string,
 *   location: string (optional),
 *   description: string (optional),
 *   event_timestamp: string (optional, default now)
 * }
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/TrackingController.php';

csrf_same_origin_guard();
require_role(['admin', 'staff']);

TrackingController::statusUpdate();
