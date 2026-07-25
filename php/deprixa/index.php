<?php
/**
 * deprixa/index.php
 *
 * Minimal login/home entry point for the Deprixa panel.
 * Replace with the full Deprixa login page in production.
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/library.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/../includes/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
    header('Location: admin.php');
    exit;
}

// Simple login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $user = db_fetch_one(
            'SELECT id, email, name, role, password FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = (string)$user['email'];
            $_SESSION['user_display_name'] = (string)$user['name'];
            $_SESSION['user_type'] = (string)$user['role'];
            header('Location: admin.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deprixa — Login</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); width: 100%; max-width: 360px; }
        .login-card h1 { font-size: 1.25rem; margin: 0 0 1rem; }
        .login-card input { width: 100%; padding: .5rem .75rem; margin-bottom: .75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .login-card button { width: 100%; padding: .6rem; background: #D62B2B; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; }
        .error { color: #D62B2B; font-size: .875rem; margin-bottom: .75rem; }
    </style>
</head>
<body>
    <form class="login-card" method="post">
        <h1>Deprixa Panel Login</h1>
        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign in</button>
    </form>
</body>
</html>
