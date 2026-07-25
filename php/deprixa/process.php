<?php
/**
 * deprixa/process.php
 *
 * Minimal action router for the Deprixa panel (logout, profile updates, etc.).
 * Replace with the full Deprixa process router in production.
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/library.php';

$action = $_GET['action'] ?? '';

if ($action === 'logOut') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($action === 'change-profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    $session_token   = $_SESSION['csrf_token'] ?? '';

    if (!hash_equals($session_token, $submitted_token)) {
        header('Location: profile_customer.php?error=' . urlencode('Invalid security token. Please refresh and try again.'));
        exit;
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name !== '' && $email !== '') {
            dbQuery('UPDATE users SET name = ' . sqlValue($name, 'str') . ', email = ' . sqlValue($email, 'str') . ' WHERE id = ' . (int)$userId);
            $_SESSION['user_name'] = $email;
            $_SESSION['user_display_name'] = $name;
        }
    }
    header('Location: profile_customer.php');
    exit;
}

http_response_code(404);
echo 'Action not found.';
