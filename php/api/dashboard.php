<?php
/**
 * Dashboard stats API
 *
 * GET /api/dashboard.php
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../config/db.php';

require_role(['admin', 'staff']);

$stats = [
    'total_shipments' => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM shipments')['c'] ?? 0),
    'in_transit'      => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM shipments WHERE status = "in_transit"')['c'] ?? 0),
    'delivered'       => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM shipments WHERE status = "delivered"')['c'] ?? 0),
    'pending'         => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM shipments WHERE status = "pending"')['c'] ?? 0),
    'customers'       => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM users WHERE role = "customer" AND is_active = 1')['c'] ?? 0),
    'online_bookings' => (int)(db_fetch_one('SELECT COUNT(*) AS c FROM courier_online')['c'] ?? 0),
];

$recent = db_fetch_all(
    'SELECT id, tracking_number, status, service_type, origin_city, destination_city, created_at
     FROM shipments
     ORDER BY created_at DESC
     LIMIT 10'
);

json_success([
    'stats' => $stats,
    'recent_shipments' => $recent,
]);
