<?php
/**
 * Schema Sync Tool
 * ----------------
 * Safely brings the live `shipping_db` database in line with the canonical
 * definition in database/dbs.sql. It only ever ADDS missing columns — it
 * never drops or alters existing ones — so it is safe to run repeatedly.
 *
 * Usage:
 *   - Web:  visit admin/sync_schema.php (admin area)
 *   - CLI:  php admin/sync_schema.php
 *
 * This addresses recurring "Unknown column" errors (e.g. payments.paid_at,
 * shipments.service_type) caused by live databases that were created from an
 * older or partial schema.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/../database/dbs.sql';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Schema Sync Tool ===\n";
echo "Database: {$db_name} @ {$db_host}\n\n";

if (!file_exists($sqlFile)) {
    die("ERROR: Cannot find schema file at {$sqlFile}\n");
}

$sql = file_get_contents($sqlFile);

// Parse CREATE TABLE blocks into [table => [column => definition]]
$tables = parseCreateTables($sql);

if (empty($tables)) {
    die("ERROR: No CREATE TABLE statements parsed from schema file.\n");
}

$applied = 0;
$skipped = 0;

foreach ($tables as $table => $columns) {
    // Skip tables that do not yet exist in the live DB.
    $exists = false;
    try {
        $db->query("SELECT 1 FROM `{$table}` LIMIT 1");
        $exists = true;
    } catch (Exception $e) {
        $exists = false;
    }

    if (!$exists) {
        echo "[skip] table `{$table}` does not exist in live DB (create it from dbs.sql first).\n";
        $skipped++;
        continue;
    }

    $liveCols = [];
    foreach ($db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $liveCols[strtolower($c['Field'])] = true;
    }

    foreach ($columns as $colName => $def) {
        if (isset($liveCols[strtolower($colName)])) {
            continue; // already present
        }
        $sqlAlter = "ALTER TABLE `{$table}` ADD COLUMN {$def}";
        try {
            $db->exec($sqlAlter);
            echo "[add ] `{$table}`.`{$colName}`\n";
            $applied++;
        } catch (Exception $e) {
            echo "[fail] `{$table}`.`{$colName}` -> " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Done. Columns added: {$applied}, tables skipped: {$skipped} ===\n";

/**
 * Parse all CREATE TABLE statements and return their column definitions.
 * Only single-line column definitions (the common case in dbs.sql) are
 * captured; INDEX/CONSTRAINT/KEY lines are ignored because this tool only
 * adds columns.
 */
function parseCreateTables($sql) {
    $tables = [];
    $lines = explode("\n", $sql);
    $i = 0;
    $count = count($lines);

    while ($i < $count) {
        $line = rtrim($lines[$i]);
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\(/i', $line, $m)) {
            $table = $m[1];
            $columns = [];
            $i++;
            // Read until the closing ")" that ends the column list.
            while ($i < $count) {
                $colLine = trim($lines[$i]);
                if ($colLine === '' || strpos($colLine, '--') === 0) {
                    $i++;
                    continue;
                }
                // Stop at the closing parenthesis of the table definition.
                if ($colLine === ')' || preg_match('/^\)\s*(ENGINE|DEFAULT|COMMENT|;)/i', $colLine)) {
                    break;
                }
                // Skip index / constraint definitions (they start with a keyword).
                if (preg_match('/^(PRIMARY\s+KEY|UNIQUE\s+KEY|KEY|INDEX|CONSTRAINT|FOREIGN\s+KEY|FULLTEXT|SPATIAL)/i', $colLine)) {
                    $i++;
                    continue;
                }
                // Capture a column definition: starts with a backtick-quoted name.
                if (preg_match('/^`([a-zA-Z0-9_]+)`\s+(.*)$/', $colLine, $cm)) {
                    $name = $cm[1];
                    $rest = rtrim($cm[2], ',');
                    // Rebuild a standalone column definition.
                    $columns[$name] = "`{$name}` " . $rest;
                }
                $i++;
            }
            $tables[$table] = $columns;
        }
        $i++;
    }

    return $tables;
}
