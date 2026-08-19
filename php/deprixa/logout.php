<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
$db = getDB();

$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$user_id = $_SESSION['admin_id'] ?? null;
$username = $_SESSION['admin_username'] ?? 'unknown';

try {
    if ($user_id) {
        $db->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, description, ip_address, user_agent, created_at)
            VALUES (:uid, 'logout', 'auth', :desc, :ip, :ua, NOW())
        ")->execute([
            ':uid' => $user_id,
            ':desc' => "User logout: $username",
            ':ip' => $ip_address,
            ':ua' => substr($user_agent, 0, 255)
        ]);
    }
} catch (Exception $e) {
    error_log("Logout audit failed: " . $e->getMessage());
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header('Location: login.php?logged_out=1');
exit;
