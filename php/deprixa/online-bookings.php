<?php
/**
 * deprixa/online-bookings.php
 *
 * Minimal online bookings list for the Deprixa panel.
 * Replace with the full Deprixa online-bookings page in production.
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/library.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

$bookings = db_fetch_all('SELECT * FROM online_booking ORDER BY booking_date DESC LIMIT 100');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deprixa — Online Bookings</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #1e2433; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #FFCC00; text-decoration: none; font-weight: 600; }
        main { max-width: 960px; margin: 2rem auto; padding: 0 1.5rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: .875rem; }
        th { background: #f9fafb; font-weight: 600; }
    </style>
</head>
<body>
    <header>
        <span>Deprixa — Online Bookings</span>
        <a href="admin.php">Back to Dashboard</a>
    </header>
    <main>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Service</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= h($b['id'] ?? '') ?></td>
                    <td><?= h($b['name'] ?? '') ?></td>
                    <td><?= h($b['email'] ?? '') ?></td>
                    <td><?= h($b['service'] ?? '') ?></td>
                    <td><?= h($b['booking_date'] ?? '') ?></td>
                    <td><?= h($b['status'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
