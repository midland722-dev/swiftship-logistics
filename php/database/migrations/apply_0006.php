<?php
/**
 * Migration 0006 applier — idempotent, mirrors existing apply_0004.php.
 * Runs the SQL file through the live PDO connection so it respects the
 * project's runtime DB credentials (no need for mysql CLI).
 *
 * Parses DELIMITER-aware statements so the CREATE PROCEDURE blocks (which
 * contain internal semicolons) are executed as single statements.
 */

require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$sqlFile = __DIR__ . '/0006_integration_layer.sql';
$sql = @file_get_contents($sqlFile);
if ($sql === false) {
    die("Cannot read migration file: $sqlFile\n");
}

// Split into statements honouring DELIMITER changes.
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
    // End of statement when the line ends with the active delimiter.
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

echo "Migration 0006 applied. statements_ok=$ok warnings_or_skipped=$fail\n";
