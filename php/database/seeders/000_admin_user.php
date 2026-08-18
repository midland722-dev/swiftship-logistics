<?php
/**
 * Seeder: default admin user
 *
 * Creates a default admin account if none exists.
 * The initial password is randomly generated and printed ONCE to stderr.
 * Change it after first login. Existing admin passwords are never overwritten.
 */

require_once __DIR__ . '/../config/db.php';

$email = 'admin@ascl-logistics.com';
$name = 'Admin User';

$existing = db_fetch_one('SELECT id FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
if ($existing) {
    // Never overwrite an existing admin password.
    fwrite(STDERR, "Admin user already exists (id: {$existing['id']}). Leaving password unchanged.\n");
    exit;
}

$password = bin2hex(random_bytes(12)); // 24-char random password
$hash = password_hash($password, PASSWORD_BCRYPT);

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

fwrite(STDERR, "Admin user created.\n");
fwrite(STDERR, "Email: {$email}\n");
fwrite(STDERR, "Temporary password: {$password}  (change it after first login)\n");
