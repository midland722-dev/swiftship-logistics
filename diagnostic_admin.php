<?php
/**
 * Admin Login Diagnostic & Fix Tool
 * ----------------------------------
 * Shows exactly what's in the database and fixes the admin password.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$email = 'admin@ascl-logistics.com';
$password = 'admin123';
$new_hash = password_hash($password, PASSWORD_BCRYPT);

$db = getDB();
$output = [];

header('Content-Type: text/plain; charset=utf-8');

try {
    // Check what tables exist
    $tables = $db->query("SHOW TABLES LIKE 'manager_admin'")->fetchAll(PDO::FETCH_COLUMN);
    $has_manager_admin = count($tables) > 0;
    $output[] = "manager_admin table exists: " . ($has_manager_admin ? 'YES' : 'NO');

    $tables = $db->query("SHOW TABLES LIKE 'users'")->fetchAll(PDO::FETCH_COLUMN);
    $has_users = count($tables) > 0;
    $output[] = "users table exists: " . ($has_users ? 'YES' : 'NO');

    // Check manager_admin
    if ($has_manager_admin) {
        try {
            $stmt = $db->prepare('SELECT id, username, email, password, full_name, role, is_active FROM manager_admin WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                $output[] = "\n=== manager_admin record ===";
                $output[] = "ID: " . $admin['id'];
                $output[] = "Username: " . $admin['username'];
                $output[] = "Email: " . $admin['email'];
                $output[] = "Full Name: " . $admin['full_name'];
                $output[] = "Role: " . $admin['role'];
                $output[] = "Is Active: " . $admin['is_active'];
                $output[] = "Password Hash: " . $admin['password'];
                $output[] = "Hash Length: " . strlen($admin['password']);
                
                // Test if current hash matches 'password' (the old default)
                $output[] = "Matches old 'password' hash: " . (password_verify('password', $admin['password']) ? 'YES' : 'NO');
                $output[] = "Matches 'admin123' hash: " . (password_verify('admin123', $admin['password']) ? 'YES' : 'NO');
                
                // Update to new hash
                $stmt = $db->prepare('UPDATE manager_admin SET password = :password WHERE email = :email');
                $stmt->execute([':password' => $new_hash, ':email' => $email]);
                $output[] = "\n>>> Updated manager_admin password to 'admin123' hash";
            } else {
                $output[] = "\nNo manager_admin record found for $email";
                
                // Check if any admin exists
                $stmt = $db->query('SELECT id, email FROM manager_admin LIMIT 5');
                $all_admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($all_admins) {
                    $output[] = "Existing manager_admin users:";
                    foreach ($all_admins as $a) {
                        $output[] = "  - ID: {$a['id']}, Email: {$a['email']}";
                    }
                }
            }
        } catch (Exception $e) {
            $output[] = "\nERROR querying manager_admin: " . $e->getMessage();
        }
    }

    // Check users table
    if ($has_users) {
        try {
            $stmt = $db->prepare('SELECT id, name, email, password, role, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $output[] = "\n=== users record ===";
                $output[] = "ID: " . $user['id'];
                $output[] = "Name: " . $user['name'];
                $output[] = "Email: " . $user['email'];
                $output[] = "Role: " . $user['role'];
                $output[] = "Is Active: " . $user['is_active'];
                $output[] = "Password Hash: " . $user['password'];
                $output[] = "Hash Length: " . strlen($user['password']);
                $output[] = "Matches 'admin123' hash: " . (password_verify('admin123', $user['password']) ? 'YES' : 'NO');
                
                // Update to new hash
                $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
                $stmt->execute([':password' => $new_hash, ':email' => $email]);
                $output[] = "\n>>> Updated users password to 'admin123' hash";
            } else {
                $output[] = "\nNo users record found for $email";
                
                // Check all users
                $stmt = $db->query('SELECT id, email, role FROM users LIMIT 10');
                $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($all_users) {
                    $output[] = "Existing users:";
                    foreach ($all_users as $u) {
                        $output[] = "  - ID: {$u['id']}, Email: {$u['email']}, Role: {$u['role']}";
                    }
                }
            }
        } catch (Exception $e) {
            $output[] = "\nERROR querying users: " . $e->getMessage();
        }
    }

    $output[] = "\n=== Final Test ===";
    $output[] = "New hash for 'admin123': " . $new_hash;
    $output[] = "Verify 'admin123' against new hash: " . (password_verify('admin123', $new_hash) ? 'PASS' : 'FAIL');
    $output[] = "\nFix complete. Try logging in with:";
    $output[] = "Email: $email";
    $output[] = "Password: $password";

} catch (Exception $e) {
    $output[] = "\nFATAL ERROR: " . $e->getMessage();
}

echo implode("\n", $output);
