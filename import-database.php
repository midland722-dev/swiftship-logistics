<?php
/**
 * One-time database import script for Railway deployment.
 * 
 * Usage:
 *   1. Upload this file to your Railway PHP service root
 *   2. Visit: https://your-railway-url.up.railway.app/import-database.php
 *   3. Delete this file after import completes
 * 
 * SECURITY: This script deletes itself after successful import.
 */

$dbUrl = getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL') ?: '';
if ($dbUrl && str_starts_with($dbUrl, 'mysql://')) {
    $url = parse_url($dbUrl);
    $dbHost = $url['host'] ?? 'localhost';
    $dbPort = $url['port'] ?? '3306';
    $dbName = ltrim($url['path'] ?? '', '/');
    $dbUser = $url['user'] ?? 'root';
    $dbPass = $url['pass'] ?? '';
} else {
    $dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'mysql.railway.internal';
    $dbPort = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
    $dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
    $dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
    $dbPass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
}

if (empty($dbPass)) {
    die("Error: Database password not found in environment variables.\n");
}

$sqlFile = __DIR__ . '/database/dbs.sql';

if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at: $sqlFile\n");
}

echo "Connecting to MySQL at $dbHost:$dbPort...\n";

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    echo "Connected successfully.\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "Reading SQL file: $sqlFile\n";
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("Error: Could not read SQL file.\n");
}

echo "Importing database...\n";
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

foreach ($queries as $query) {
    if (empty($query)) continue;
    
    try {
        $pdo->exec($query);
        $success++;
    } catch (PDOException $e) {
        $errors++;
        echo "Error in query: " . $e->getMessage() . "\n";
        echo "Query: " . substr($query, 0, 100) . "...\n\n";
    }
}

echo "\nImport completed.\n";
echo "Successful queries: $success\n";
echo "Failed queries: $errors\n";

// Self-destruct for security
$self = __FILE__;
if (file_exists($self)) {
    unlink($self);
    echo "\nSecurity: Import script has been deleted.\n";
}

echo "\nNext steps:\n";
echo "1. Verify your admin panel at: https://ascl-logistics.com/admin\n";
echo "2. Check database tables were created correctly\n";
echo "3. Remove any remaining import scripts from your public directory\n";
