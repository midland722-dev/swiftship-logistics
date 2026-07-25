<?php
/**
 * deprixa/add-courier.php
 *
 * Minimal add-shipment form for the Deprixa panel.
 * Creates records in both courier_online (legacy) and shipments (modern).
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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $name         = trim($_POST['name'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $address      = trim($_POST['address'] ?? '');
        $type         = trim($_POST['type'] ?? 'parcel');
        $service      = trim($_POST['service'] ?? 'standard');
        $office       = trim($_POST['officename'] ?? $_SESSION['user_type'] ?? '');
        $destination  = trim($_POST['destination'] ?? '');
        $weight       = trim($_POST['weight'] ?? '');
        $receiverName = trim($_POST['receiver_name'] ?? '');

        if ($name === '' || $email === '' || $phone === '') {
            $error = 'Name, email, and phone are required.';
        } else {
            $consNo = 'CONS-' . strtoupper(bin2hex(random_bytes(3)));

            dbQuery('INSERT INTO courier_online (cons_no, ship_name, email, s_phone, s_add, type, service, status, officename, date) VALUES (' .
                sqlValue($consNo, 'str') . ', ' .
                sqlValue($name, 'str') . ', ' . sqlValue($email, 'str') . ', ' . sqlValue($phone, 'str') . ', ' .
                sqlValue($address, 'str') . ', ' . sqlValue($type, 'str') . ', ' . sqlValue($service, 'str') . ', ' .
                sqlValue('Pending', 'str') . ', ' . sqlValue($office, 'str') . ', NOW())');

            $trackingNumber = generate_tracking_number();
            $originCountry  = 'US';
            $originCity     = $office ?: 'Unknown';
            $destCountry    = $destination ? 'US' : 'US';
            $destCity       = $destination ?: 'Unknown';

            db_execute(
                'INSERT INTO shipments
                    (tracking_number, status, service_type, origin_country, origin_city, destination_country, destination_city,
                     total_weight, pieces,
                     sender_name, sender_email, sender_phone, sender_address, sender_city, sender_state, sender_country,
                     receiver_name, receiver_city, receiver_country,
                     created_by, created_at, updated_at)
                 VALUES
                    (:tracking_number, :status, :service_type, :origin_country, :origin_city, :destination_country, :destination_city,
                     :total_weight, :pieces,
                     :sender_name, :sender_email, :sender_phone, :sender_address, :sender_city, :sender_state, :sender_country,
                     :receiver_name, :receiver_city, :receiver_country,
                     :created_by, NOW(), NOW())',
                [
                    ':tracking_number' => $trackingNumber,
                    ':status'          => 'pending',
                    ':service_type'    => $service,
                    ':origin_country'  => $originCountry,
                    ':origin_city'     => $originCity,
                    ':destination_country' => $destCountry,
                    ':destination_city'    => $destCity,
                    ':total_weight'    => $weight !== '' ? (float)$weight : null,
                    ':pieces'          => 1,
                    ':sender_name'     => $name,
                    ':sender_email'    => $email,
                    ':sender_phone'    => $phone,
                    ':sender_address'  => $address,
                    ':sender_city'     => $originCity,
                    ':sender_state'    => '',
                    ':sender_country'  => $originCountry,
                    ':receiver_name'   => $receiverName ?: '',
                    ':receiver_city'   => $destCity,
                    ':receiver_country'=> $destCountry,
                    ':created_by'      => (int)($_SESSION['user_id'] ?? 0),
                ]
            );

            header('Location: admin.php?ok=1&tracking=' . urlencode($trackingNumber));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deprixa — Add Shipping</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #1e2433; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #FFCC00; text-decoration: none; font-weight: 600; }
        main { max-width: 640px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        label { display: block; font-size: .875rem; font-weight: 600; margin: .75rem 0 .25rem; }
        input, select { width: 100%; padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        button { margin-top: 1rem; width: 100%; padding: .6rem; background: #D62B2B; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; }
        .error { color: #D62B2B; font-size: .875rem; margin-bottom: .75rem; }
        .success { color: #065f46; font-size: .875rem; margin-bottom: .75rem; background: #d1fae5; padding: .5rem .75rem; border-radius: 4px; }
        .help { font-size: .75rem; color: #6b7280; margin-top: .25rem; }
    </style>
</head>
<body>
    <header>
        <span>Deprixa — Add Shipping</span>
        <a href="admin.php">Dashboard</a>
    </header>
    <main>
        <form class="card" method="post">
            <h1 style="margin-top:0">New Shipment</h1>
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <?php if ($error): ?>
                <p class="error"><?= h($error) ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['ok']) && isset($_GET['tracking'])): ?>
                <p class="success">Shipment created. Tracking number: <strong><?= h($_GET['tracking']) ?></strong></p>
            <?php endif; ?>
            <label>Sender Name *</label>
            <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
            <label>Phone *</label>
            <input type="text" name="phone" required value="<?= h($_POST['phone'] ?? '') ?>">
            <label>Address</label>
            <input type="text" name="address" value="<?= h($_POST['address'] ?? '') ?>">
            <label>Destination</label>
            <input type="text" name="destination" placeholder="City, Country" value="<?= h($_POST['destination'] ?? '') ?>">
            <label>Receiver Name</label>
            <input type="text" name="receiver_name" placeholder="Receiver name" value="<?= h($_POST['receiver_name'] ?? '') ?>">
            <label>Weight (kg)</label>
            <input type="number" name="weight" step="0.1" min="0.1" value="<?= h($_POST['weight'] ?? '') ?>">
            <label>Type</label>
            <select name="type">
                <option value="parcel" <?= (($_POST['type'] ?? 'parcel') === 'parcel') ? 'selected' : '' ?>>Parcel</option>
                <option value="document" <?= (($_POST['type'] ?? '') === 'document') ? 'selected' : '' ?>>Document</option>
                <option value="freight" <?= (($_POST['type'] ?? '') === 'freight') ? 'selected' : '' ?>>Freight</option>
            </select>
            <label>Service</label>
            <select name="service">
                <option value="standard" <?= (($_POST['service'] ?? 'standard') === 'standard') ? 'selected' : '' ?>>Standard</option>
                <option value="express" <?= (($_POST['service'] ?? '') === 'express') ? 'selected' : '' ?>>Express</option>
                <option value="economy" <?= (($_POST['service'] ?? '') === 'economy') ? 'selected' : '' ?>>Economy</option>
            </select>
            <button type="submit">Create Shipment</button>
        </form>
    </main>
</body>
</html>
