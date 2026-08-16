<?php
/**
 * Direct Database Fix for Admin Login
 * ------------------------------------
 * This script directly updates the database with the correct password hash.
 * It provides detailed output so you can see exactly what's happening.
 */

$email = 'admin@ascl-logistics.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "=== Admin Login Fix Tool ===\n\n";
echo "Target email: $email\n";
echo "New password: $password\n";
echo "Generated hash: $hash\n\n";

try {
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/db.php';
    
    echo "[1] Database connection: OK\n";
    
    $db = getDB();
    
    // Check manager_admin table
    echo "\n[2] Checking manager_admin table...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'manager_admin'");
    $table_exists = $stmt->fetchColumn();
    
    if ($table_exists) {
        echo "    Table exists: YES\n";
        
        // Check columns
        $stmt = $db->query("SHOW COLUMNS FROM manager_admin LIKE 'password'");
        $has_password_col = $stmt->fetchColumn();
        echo "    Has 'password' column: " . ($has_password_col ? 'YES' : 'NO') . "\n";
        
        $stmt = $db->query("SHOW COLUMNS FROM manager_admin LIKE 'pwd'");
        $has_pwd_col = $stmt->fetchColumn();
        echo "    Has 'pwd' column: " . ($has_pwd_col ? 'YES' : 'NO') . "\n";
        
        // Try to find the user
        $stmt = $db->prepare('SELECT id, email, password FROM manager_admin WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    Found user: YES (ID: {$user['id']})\n";
            echo "    Current hash: " . substr($user['password'], 0, 30) . "...\n";
            echo "    Hash length: " . strlen($user['password']) . "\n";
            
            // Test old hash
            $old_matches = password_verify('password', $user['password']);
            echo "    Matches old 'password': " . ($old_matches ? 'YES' : 'NO') . "\n";
            
            $new_matches = password_verify('admin123', $user['password']);
            echo "    Matches 'admin123': " . ($new_matches ? 'YES' : 'NO') . "\n";
            
            // Update
            $col = $has_password_col ? 'password' : ($has_pwd_col ? 'pwd' : 'password');
            $stmt = $db->prepare("UPDATE manager_admin SET $col = :hash WHERE email = :email");
            $result = $stmt->execute([':hash' => $hash, ':email' => $email]);
            
            if ($result) {
                echo "    >>> Updated $col column with new hash\n";
                
                // Verify
                $stmt = $db->prepare("SELECT password FROM manager_admin WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $verify = $stmt->fetchColumn();
                echo "    Verification: " . (password_verify('admin123', $verify) ? 'PASS' : 'FAIL') . "\n";
            } else {
                echo "    >>> UPDATE FAILED\n";
            }
        } else {
            echo "    Found user: NO\n";
            echo "    Searching all manager_admin users...\n";
            $stmt = $db->query('SELECT id, email FROM manager_admin');
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($all as $u) {
                echo "      - ID: {$u['id']}, Email: {$u['email']}\n";
            }
            
            // Insert new user
            echo "\n    Inserting new admin user...\n";
            $stmt = $db->prepare('INSERT INTO manager_admin (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)');
            $result = $stmt->execute(['admin', $email, $hash, 'Admin User', 'admin']);
            echo "    >>> Inserted: " . ($result ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "    Table exists: NO\n";
        echo "    Skipping manager_admin\n";
    }
    
    // Check users table
    echo "\n[3] Checking users table...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $table_exists = $stmt->fetchColumn();
    
    if ($table_exists) {
        echo "    Table exists: YES\n";
        
        $stmt = $db->prepare('SELECT id, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    Found user: YES (ID: {$user['id']}, Role: {$user['role']})\n";
            echo "    Current hash: " . substr($user['password'], 0, 30) . "...\n";
            
            $matches = password_verify('admin123', $user['password']);
            echo "    Matches 'admin123': " . ($matches ? 'YES' : 'NO') . "\n";
            
            if (!$matches) {
                $stmt = $db->prepare('UPDATE users SET password = :hash WHERE email = :email');
                $result = $stmt->execute([':hash' => $hash, ':email' => $email]);
                echo "    >>> Updated password: " . ($result ? 'YES' : 'NO') . "\n";
                
                // Verify
                $stmt = $db->prepare('SELECT password FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $verify = $stmt->fetchColumn();
                echo "    Verification: " . (password_verify('admin123', $verify) ? 'PASS' : 'FAIL') . "\n";
            }
        } else {
            echo "    Found user: NO\n";
            echo "    Inserting new user...\n";
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)');
            $result = $stmt->execute(['Admin User', $email, $hash, 'admin']);
            echo "    >>> Inserted: " . ($result ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "    Table exists: NO\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "Fix complete. Try logging in with:\n";
    echo "  Email:    $email\n";
    echo "  Password: $password\n";
    echo "\nIf login still fails, check the output above for clues.\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
