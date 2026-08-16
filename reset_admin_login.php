<?php
/**
 * Self-deleting admin password reset script.
 *
 * Access once via browser, then it removes itself automatically.
 */

$email = 'admin@ascl-logistics.com';
$password = 'admin123';

try {
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/db.php';

    $db = getDB();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $db->beginTransaction();

    $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
    $stmt->execute([':password' => $hash, ':email' => $email]);

    $stmt = $db->prepare('UPDATE manager_admin SET password = :password WHERE email = :email');
    $stmt->execute([':password' => $hash, ':email' => $email]);

    $db->commit();

    header('Content-Type: text/plain; charset=utf-8');
    echo "OK\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    @unlink(__FILE__);
}
