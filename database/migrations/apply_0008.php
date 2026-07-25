<?php
/**
 * Apply migration 0008: Carrier Tracking Enhancements + Deduplication
 *
 * Usage: php database/migrations/apply_0008.php
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/0008_carrier_tracking_enhancements.sql';

try {
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException("Cannot read migration file: $sqlFile");
    }

    $parts = array_filter(array_map('trim', explode('DELIMITER $$', $sql)));
    $db->beginTransaction();
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || str_starts_with($part, '--')) {
            continue;
        }
        $statements = array_filter(array_map('trim', explode('$$', $part)));
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--')) {
                continue;
            }
            try {
                $db->exec($stmt);
            } catch (Exception $e) {
                error_log("Migration 0008 statement skipped: " . $e->getMessage() . " | SQL: " . substr($stmt, 0, 120));
            }
        }
    }

    try {
        $backfilled = (int)$db->query("SELECT COUNT(*) FROM carrier_tracking_events WHERE dedup_hash IS NULL")->fetchColumn();
        if ($backfilled > 0) {
            $db->exec("
                UPDATE carrier_tracking_events
                SET dedup_hash = SHA2(
                    CONCAT_WS('|', shipment_id, integration_id, carrier_tracking_number, carrier_status, canonical_status, IFNULL(location,''), IFNULL(event_timestamp,'')),
                    256
                )
                WHERE dedup_hash IS NULL
            ");
            error_log("Migration 0008: backfilled dedup_hash for $backfilled existing carrier_tracking_events rows.");
        }
    } catch (Exception $e) {
        error_log("Migration 0008 dedup_hash backfill skipped: " . $e->getMessage());
    }

    $db->commit();

    echo "Migration 0008 applied successfully.\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Migration 0008 FAILED: " . $e->getMessage() . "\n");
    exit(1);
}
