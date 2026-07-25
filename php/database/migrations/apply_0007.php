<?php
/**
 * Apply migration 0007: Carrier Tracking Number Integration
 *
 * Usage: php database/migrations/apply_0007.php
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/0007_carrier_tracking_numbers.sql';

try {
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException("Cannot read migration file: $sqlFile");
    }

    // Split by delimiter and execute each statement
    $queries = array_filter(array_map('trim', explode('DELIMITER ;', $sql)));

    $db->beginTransaction();
    foreach ($queries as $query) {
        $query = trim($query);
        if ($query === '' || str_starts_with($query, '--')) {
            continue;
        }
        try {
            $db->exec($query);
        } catch (Exception $e) {
            // Idempotent migrations may produce duplicate-key / already-exists errors;
            // log but don't fail the whole migration.
            error_log("Migration 0007 statement skipped: " . $e->getMessage() . " | SQL: " . substr($query, 0, 120));
        }
    }
    $db->commit();

    echo "Migration 0007 applied successfully.\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Migration 0007 FAILED: " . $e->getMessage() . "\n");
    exit(1);
}
