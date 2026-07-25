<?php
/**
 * Migration 0016 applier — idempotent.
 *
 * Adds missing production indexes for common query patterns.
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();

$ok = 0;
$skipped = 0;

function step(string $label, callable $work): void {
    global $ok, $skipped;
    try {
        $work();
        $ok++;
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (preg_grep('/already exists|Duplicate|Unknown (column|key)|1060|1061|1062|1068|1072|1091/i', [$msg])) {
            $skipped++;
        } else {
            fwrite(STDERR, "WARN [$label]: " . $msg . "\n");
            $skipped++;
        }
    }
}

step('idx_shipments_status_customer', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'shipments'
          AND INDEX_NAME = 'idx_shipments_status_customer'
    ")->fetch();
    if (!$row) {
        $db->exec('ALTER TABLE `shipments` ADD INDEX `idx_shipments_status_customer` (`status`, `customer_id`, `created_at`)');
    }
});

step('idx_shipments_tracking_status', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'shipments'
          AND INDEX_NAME = 'idx_shipments_tracking_status'
    ")->fetch();
    if (!$row) {
        $db->exec('ALTER TABLE `shipments` ADD INDEX `idx_shipments_tracking_status` (`tracking_number`, `status`)');
    }
});

step('idx_contact_messages_status', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'contact_messages'
          AND INDEX_NAME = 'idx_contact_messages_status'
    ")->fetch();
    if (!$row) {
        $db->exec('ALTER TABLE `contact_messages` ADD INDEX `idx_contact_messages_status` (`status`, `created_at`)');
    }
});

step('idx_users_role_active', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND INDEX_NAME = 'idx_users_role_active'
    ")->fetch();
    if (!$row) {
        $db->exec('ALTER TABLE `users` ADD INDEX `idx_users_role_active` (`role`, `is_active`)');
    }
});

echo "Migration 0016 applied. steps_ok=$ok skipped=$skipped\n";
