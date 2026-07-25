<?php
/**
 * Migration 0015 applier — idempotent.
 *
 * Hardens tracking_history for production without relying on stored
 * procedures, making it safe for shared hosting environments where
 * the MySQL user may lack CREATE ROUTINE / ALTER ROUTINE privileges.
 *
 * Changes:
 *   1. Adds transit_location column if missing.
 *   2. Adds composite indexes if missing.
 *   3. Adds foreign keys only if no orphaned rows exist.
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

// ------------------------------------------------------------------
// 1) Add transit_location column if missing
// ------------------------------------------------------------------
step('transit_location column', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND COLUMN_NAME = 'transit_location'
    ")->fetch();

    if (!$row) {
        $db->exec("
            ALTER TABLE `tracking_history`
                ADD COLUMN `transit_location` varchar(255) DEFAULT NULL
                AFTER `location`
        ");
    }
});

// ------------------------------------------------------------------
// 2) Add composite index: (tracking_number, event_timestamp)
// ------------------------------------------------------------------
step('idx_tracking_history_number_ts', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND INDEX_NAME = 'idx_tracking_history_number_ts'
    ")->fetch();

    if (!$row) {
        $db->exec("
            ALTER TABLE `tracking_history`
                ADD INDEX `idx_tracking_history_number_ts` (`tracking_number`, `event_timestamp`)
        ");
    }
});

// ------------------------------------------------------------------
// 3) Add composite index: (shipment_id, event_timestamp DESC)
// ------------------------------------------------------------------
step('idx_tracking_history_shipment_ts', function () use ($db) {
    $row = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND INDEX_NAME = 'idx_tracking_history_shipment_ts'
    ")->fetch();

    if (!$row) {
        $db->exec("
            ALTER TABLE `tracking_history`
                ADD INDEX `idx_tracking_history_shipment_ts` (`shipment_id`, `event_timestamp` DESC)
        ");
    }
});

// ------------------------------------------------------------------
// 4) Add FK: shipment_id -> shipments(id) ON DELETE CASCADE
// ------------------------------------------------------------------
step('fk_tracking_history_shipment', function () use ($db) {
    $idx = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND INDEX_NAME = 'fk_tracking_history_shipment'
    ")->fetch();

    if ($idx) {
        return;
    }

    $orphan = $db->query("
        SELECT COUNT(*) AS c
        FROM `tracking_history` th
        LEFT JOIN `shipments` s ON s.id = th.`shipment_id`
        WHERE s.id IS NULL
    ")->fetch();

    if ((int)($orphan['c'] ?? 0) === 0) {
        $db->exec("
            ALTER TABLE `tracking_history`
                ADD CONSTRAINT `fk_tracking_history_shipment`
                FOREIGN KEY (`shipment_id`)
                REFERENCES `shipments` (`id`)
                ON DELETE CASCADE
        ");
    }
});

// ------------------------------------------------------------------
// 5) Add FK: updated_by -> users(id) ON DELETE SET NULL
// ------------------------------------------------------------------
step('fk_tracking_history_updated_by', function () use ($db) {
    $idx = $db->query("
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND INDEX_NAME = 'fk_tracking_history_updated_by'
    ")->fetch();

    if ($idx) {
        return;
    }

    $orphan = $db->query("
        SELECT COUNT(*) AS c
        FROM `tracking_history` th
        LEFT JOIN `users` u ON u.id = th.`updated_by`
        WHERE th.`updated_by` IS NOT NULL
          AND u.id IS NULL
    ")->fetch();

    if ((int)($orphan['c'] ?? 0) === 0) {
        $db->exec("
            ALTER TABLE `tracking_history`
                ADD CONSTRAINT `fk_tracking_history_updated_by`
                FOREIGN KEY (`updated_by`)
                REFERENCES `users` (`id`)
                ON DELETE SET NULL
        ");
    }
});

echo "Migration 0015 applied. steps_ok=$ok skipped=$skipped\n";
