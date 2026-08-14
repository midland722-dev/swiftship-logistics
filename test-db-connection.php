<?php
/**
 * Railway Database Connection Test
 * 
 * Visit this file on your Railway deployment to verify database connection:
 * https://your-project.up.railway.app/test-db-connection.php
 * 
 * DELETE THIS FILE AFTER TESTING - it exposes database information.
 */

header('Content-Type: text/plain');

echo "=== Railway Database Connection Test ===\n\n";

// Show environment variables (without passwords)
echo "Environment Variables:\n";
echo str_repeat('-', 40) . "\n";
$vars = ['MYSQLHOST', 'MYSQLPORT', 'MYSQLDATABASE', 'MYSQLUSER', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PORT', 'APP_ENV'];
foreach ($vars as $var) {
    $val = getenv($var);
    if ($val === false) {
        echo "$var: (not set)\n";
    } else {
        echo "$var: $val\n";
    }
}
echo "\n";

// Test connection
$dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'mysql.railway.internal';
$dbPort = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
$dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$dbPass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';

echo "Testing connection to: $dbHost:$dbPort\n";
echo "Database: $dbName\n";
echo "User: $dbUser\n";
echo str_repeat('-', 40) . "\n";

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    echo "✓ Connection successful!\n\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database (" . count($tables) . "):\n";
    echo str_repeat('-', 40) . "\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    // Check if key tables exist
    $requiredTables = ['shipments', 'users', 'user_roles', 'pricing_rules', 'service_bulletins'];
    echo "\nRequired tables check:\n";
    echo str_repeat('-', 40) . "\n";
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "✓ $table exists\n";
        } else {
            echo "✗ $table MISSING\n";
        }
    }
    
    // Check users table for admin
    echo "\nChecking for admin users:\n";
    echo str_repeat('-', 40) . "\n";
    try {
        $stmt = $pdo->query("SELECT id, email, role FROM users WHERE role IN ('admin', 'staff') LIMIT 5");
        $users = $stmt->fetchAll();
        if (count($users) > 0) {
            foreach ($users as $user) {
                echo "  - ID: {$user['id']}, Email: {$user['email']}, Role: {$user['role']}\n";
            }
        } else {
            echo "  No admin/staff users found.\n";
        }
    } catch (Exception $e) {
        echo "  Error checking users: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Test Complete ===\n";
    echo "REMINDER: Delete this file after testing!\n";
    
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check Railway MySQL is running\n";
    echo "2. Verify environment variables are set correctly\n";
    echo "3. Check MySQL user permissions\n";
    exit(1);
}
