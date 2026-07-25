<?php
require_once __DIR__ . '/includes/db.php';

$sqlFile = __DIR__ . '/php/database/migrations/0015_tracking_history_production.sql';
$sql = @file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Cannot read migration file: $sqlFile\n");
    exit(1);
}

$db = getDB();
$ok = 0; $fail = 0;
foreach (explode("\n", $sql) as $line) {
    $trimmed = trim($line);
    if ($trimmed === '' || stripos($trimmed, 'DELIMITER') === 0 || stripos($trimmed, 'DROP PROCEDURE') === 0 || stripos($trimmed, 'CREATE PROCEDURE') === 0 || stripos($trimmed, 'END$$') === 0 || stripos($trimmed, 'DELIMITER ;') === 0) {
        continue;
    }
    if (preg_match('/^(CALL\s+\w+)/', $trimmed, $m)) {
        try {
            $db->exec($m[1]);
            $ok++;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (!preg_match('/already exists|Duplicate|Unknown (column|key)|1060|1061|1062|1068|1072|1091| PROCEDURE/i', $msg)) {
                fwrite(STDERR, "WARN: " . $msg . "\n");
            }
            $fail++;
        }
    }
}

echo "Migration 0015 applied. statements_ok=$ok warnings_or_skipped=$fail\n";
