<?php
/**
 * Auth API
 *
 * POST /api/auth.php                 - login
 * POST /api/auth.php?action=logout   - logout
 * GET  /api/auth.php?action=me       - current user
 */

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/rate-limit.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'me') {
    $user = db_fetch_one('SELECT id, name, email, role, is_active, created_at FROM users WHERE id = :id LIMIT 1', [':id' => $_SESSION['user_id'] ?? 0]);
    if (!$user) {
        json_error('Not authenticated.', 401, 'UNAUTHORIZED');
    }
    json_success($user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 86400, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_success(null, 200, 'Logged out.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_same_origin_guard();

    // Throttle login attempts (brute-force / enumeration protection).
    if (!rate_limit('auth_login', 10, 60)) {
        json_error('Too many attempts. Please try again later.', 429, 'RATE_LIMITED');
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $email = trim((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if ($email === '' || $password === '') {
        json_error('Email and password are required.', 422, 'VALIDATION_ERROR');
    }

    $user = db_fetch_one('SELECT * FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
    if (!$user || !password_verify($password, $user['password'])) {
        json_error('Invalid email or password.', 401, 'INVALID_CREDENTIALS');
    }

    if ((int)$user['is_active'] !== 1) {
        json_error('Account is disabled.', 403, 'ACCOUNT_DISABLED');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = (string)$user['name'];
    $_SESSION['user_type'] = (string)$user['role'];

    db_execute(
        'UPDATE users SET last_login_at = NOW() WHERE id = :id',
        [':id' => (int)$user['id']]
    );

    $safe = [
        'id' => (int)$user['id'],
        'name' => (string)$user['name'],
        'email' => (string)$user['email'],
        'role' => (string)$user['role'],
        'is_active' => (int)$user['is_active'],
    ];

    json_success($safe, 200, 'Logged in.');
}

json_error('Method not allowed.', 405, 'METHOD_NOT_ALLOWED');
