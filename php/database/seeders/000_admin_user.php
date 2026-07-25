<?php
/**
 * Seeder: default admin user
 *
 * Creates a default admin account if none exists.
 * IMPORTANT: Change the password after first login.
 */

require_once __DIR__ . '/../config/db.php';

$email = 'admin@ascl-logistics.com';
$password = 'Admin@123';
$name = 'Admin User';

$existing = db_fetch_one('SELECT id FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
if ($existing) {
    echo "Admin user already exists (id: {$existing['id']}).\n";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
db_execute(
    'INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
     VALUES (:name, :email, :password, :role, 1, NOW(), NOW())',
    [
        ':name' => $name,
        ':email' => $email,
        ':password' => $hash,
        ':role' => 'admin',
    ]
);

echo "Admin user created.\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n";
echo "Please log in at /deprixa/index.php and change the password immediately.\n";
