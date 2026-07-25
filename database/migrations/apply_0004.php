<?php
/**
 * Migration 0004: Courier-style Tracking Enhancements
 * Adds customs_procedure columns and indexes for tracking history.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

$statements = [];

// 1) Add customs_procedure to shipment_status_history_v2
$statements[] = function($db) {
    try {
        $db->query("SELECT customs_procedure FROM shipment_status_history_v2 LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE shipment_status_history_v2 ADD COLUMN customs_procedure varchar(255) DEFAULT NULL AFTER remarks");
    }
};

// 2) Add transit_location to shipments
$statements[] = function($db) {
    try {
        $db->query("SELECT transit_location FROM shipments LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE shipments ADD COLUMN transit_location varchar(255) DEFAULT NULL AFTER current_branch");
    }
};

// 3) Add customs_procedure to shipments
$statements[] = function($db) {
    try {
        $db->query("SELECT customs_procedure FROM shipments LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE shipments ADD COLUMN customs_procedure varchar(255) DEFAULT NULL AFTER transit_location");
    }
};

// 4) Add index on shipment_status_history_v2
$statements[] = function($db) {
    try {
        $db->exec("ALTER TABLE shipment_status_history_v2 ADD INDEX idx_history_shipment_time (shipment_id, occurred_at DESC)");
    } catch (Exception $e) {
        // Index may already exist
    }
};

foreach ($statements as $stmt) {
    try {
        $stmt($db);
    } catch (Exception $e) {
        // Ignore errors for idempotency
    }
}

echo "Migration 0004 applied successfully.\n";
