<?php
/**
 * deprixa/add-new-users-admin.php
 *
 * Admin user management list page.
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/library.php';

if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

$users = db_fetch_all('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deprixa — Admin Users</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #1e2433; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #FFCC00; text-decoration: none; font-weight: 600; }
        main { max-width: 960px; margin: 2rem auto; padding: 0 1.5rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: .875rem; }
        th { background: #f9fafb; font-weight: 600; }
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 6px; font-size: .875rem; font-weight: 600; text-decoration: none; background: #D62B2B; color: #fff; margin-bottom: 1rem; }
        .badge { display: inline-block; padding: .25rem .5rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <header>
        <span>Deprixa — Admin Users</span>
        <a href="admin.php">Dashboard</a>
    </header>
    <main>
        <a href="settings/addusersadmin/agregar.php" class="btn">+ Add Admin User</a>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= h($u['name']) ?></td>
                    <td><?= h($u['email']) ?></td>
                    <td><?= h($u['role']) ?></td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td><?= h($u['created_at'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
