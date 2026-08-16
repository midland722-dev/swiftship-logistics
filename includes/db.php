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

// Load .env so getenv()/$_ENV reflect project settings, not system defaults.
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$appEnv = getenv('APP_ENV') ?: 'production';
$isProduction = $appEnv === 'production';

if (!$isProduction) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: '');
    define('DB_USER', getenv('DB_USER') ?: '');
    define('DB_PASS', getenv('DB_PASS') ?: '');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: '');
    define('DB_USER', getenv('DB_USER') ?: '');
    define('DB_PASS', getenv('DB_PASS') ?: '');
}
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

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
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $detail = 'DB_CONNECTION_FAILED: ' . $e->getMessage() . ' | DSN=' . $dsn . ' user=' . DB_USER;
            @file_put_contents(__DIR__ . '/../logs/db_connect_errors.log', '[' . date('Y-m-d H:i:s') . '] ' . $detail . "\n", FILE_APPEND | LOCK_EX);
            if (function_exists('is_api_request') && is_api_request()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Database connection failed. Please contact support.']);
                exit;
            }
            http_response_code(500);
            echo '<h1>Database Connection Error</h1>';
            echo '<p>The application is unable to connect to the database. Please contact support.</p>';
            exit;
        }
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
