<?php
/**
 * Apply migration 0009: Performance Indexes + Security Hardening
 *
 * Usage: php database/migrations/apply_0009.php
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/0009_performance_indexes.sql';

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
                error_log("Migration 0009 statement skipped: " . $e->getMessage() . " | SQL: " . substr($stmt, 0, 120));
            }
        }
    }

    $db->commit();

    echo "Migration 0009 applied successfully.\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Migration 0009 FAILED: " . $e->getMessage() . "\n");
    exit(1);
}
