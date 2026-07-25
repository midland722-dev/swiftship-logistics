<?php
/**
 * Migration 0002 applier — idempotent.
 * Expands shipments.status enum and creates drivers / vehicles tables.
 *
 * Note: Additional courier columns on shipments are added by the
 * admin/sync_schema.php tool (non-destructive ALTERs). Run it after
 * this migration if those columns are still missing.
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/0002_courier_management.sql';
$sql = @file_get_contents($sqlFile);
if ($sql === false) {
    die("Cannot read migration file: $sqlFile\n");
}

$statements = [];
$delimiter = ';';
$buffer = '';
foreach (preg_split('/\r?\n/', $sql) as $line) {
    $trimmed = trim($line);
    if (preg_match('/^DELIMITER\s+(\S+)$/i', $trimmed, $m)) {
        if ($buffer !== '') {
            $statements[] = $buffer;
            $buffer = '';
        }
        $delimiter = $m[1];
        continue;
    }
    $buffer .= $line . "\n";
    if (substr(rtrim($buffer), -strlen($delimiter)) === $delimiter) {
        $statements[] = substr($buffer, 0, -strlen($delimiter));
        $buffer = '';
    }
}
if ($buffer !== '') {
    $statements[] = $buffer;
}

$ok = 0; $fail = 0;
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if ($stmt === '' || stripos($stmt, 'DELIMITER') === 0) {
        continue;
    }
    try {
        $db->exec($stmt);
        $ok++;
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (!preg_match('/already exists|Duplicate|Unknown (column|key)|1060|1061|1062|1068|1072|1091/i', $msg)) {
            fwrite(STDERR, "WARN: " . $msg . "\n");
        }
        $fail++;
    }
}

echo "Migration 0002 applied. statements_ok=$ok warnings_or_skipped=$fail\n";
