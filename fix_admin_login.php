<?php
/**
 * Admin Login Fix Script
 * -----------------------
 * Syncs the admin password between `users` and `manager_admin` tables
 * to resolve "Invalid login credentials" issues caused by stale seed data.
 *
 * Usage:
 *   Web:  visit /fix_admin_login.php in your browser
 *   CLI:  php /fix_admin_login.php (if PHP CLI is available)
 *
 * IMPORTANT: Delete this file after use for security.
 */

$email = 'admin@ascl-logistics.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();
$results = [];

try {
    $db->beginTransaction();

    $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
    $stmt->execute([':password' => $hash, ':email' => $email]);
    $results['users_updated'] = $stmt->rowCount();

    $stmt = $db->prepare('UPDATE manager_admin SET password = :password WHERE email = :email');
    $stmt->execute([':password' => $hash, ':email' => $email]);
    $results['manager_admin_updated'] = $stmt->rowCount();

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    $results['error'] = $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Admin Login Fix ===\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n\n";

if (isset($results['error'])) {
    echo "ERROR: " . $results['error'] . "\n";
} else {
    echo "users table rows updated: " . $results['users_updated'] . "\n";
    echo "manager_admin table rows updated: " . $results['manager_admin_updated'] . "\n";
    echo "\nFix applied successfully.\n";
    echo "You can now log in with:\n";
    echo "  Email:    {$email}\n";
    echo "  Password: {$password}\n";
    echo "\nREMEMBER TO DELETE THIS FILE AFTER USE.\n";
}
