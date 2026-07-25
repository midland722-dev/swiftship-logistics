<?php
/**
 * Root database bootstrap — used by migration appliers and shared scripts.
 *
 * Provides:
 *   getDB()  : singleton PDO connection
 *   db_fetch_all(string $sql, array $params = []): array
 *   db_fetch_one(string $sql, array $params = []): ?array
 *   db_execute(string $sql, array $params = []): int
 *   h(mixed $value): string
 */

// Load credentials from the project's canonical config if available.
$possibleConfigs = [
    __DIR__ . '/../php/config/db.php',
    __DIR__ . '/../../php/config/db.php',
    __DIR__ . '/config/db.php',
];
foreach ($possibleConfigs as $cfg) {
    if (file_exists($cfg)) {
        require_once $cfg;
        break;
    }
}

// Fallback constants if the config file was not found.
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'shipping_db');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}

/**
 * Returns a singleton PDO connection (legacy alias for db()).
 */
function getDB(): PDO {
    return db();
}

/**
 * Generate a unique tracking number in production format.
 */
function generate_tracking_number(): string {
    $pdo = db();
    do {
        $num = str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        $tn = 'ASC' . $num;
        $stmt = $pdo->prepare('SELECT 1 FROM shipments WHERE tracking_number = :tn LIMIT 1');
        $stmt->execute([':tn' => $tn]);
        $exists = $stmt->fetchColumn();
    } while ($exists);
    return $tn;
}

/**
 * Returns a singleton PDO connection.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

/**
 * Convenience helper — run a prepared SELECT, return all rows.
 */
function db_fetch_all(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Convenience helper — run a prepared SELECT, return one row or null.
 */
function db_fetch_one(string $sql, array $params = []): ?array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Convenience helper — run INSERT / UPDATE / DELETE.
 */
function db_execute(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $id = db()->lastInsertId();
    return $id ? (int)$id : $stmt->rowCount();
}

/**
 * Escape output safely for HTML context.
 */
function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
